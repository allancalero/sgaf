import { Injectable, NgZone } from '@angular/core';
import { Router } from '@angular/router';
import { Subject, BehaviorSubject, fromEvent, merge, timer, Subscription } from 'rxjs';
import { debounceTime, throttleTime } from 'rxjs/operators';
import { AuthService } from './auth.service';

@Injectable({
    providedIn: 'root'
})
export class InactivityService {
    // Configuration
    private readonly TIMEOUT_SECONDS = 300; // 5 minutes
    private readonly WARNING_SECONDS = 60;  // Warn at 1 minute remaining

    // State
    private lastActivity = Date.now();
    private timerSubscription?: Subscription;
    private eventsSubscription?: Subscription;
    private broadcastChannel: BroadcastChannel;

    // Observables
    public isWarning$ = new BehaviorSubject<boolean>(false);
    public countdown$ = new BehaviorSubject<number>(0);
    public isExpired$ = new Subject<void>();

    constructor(
        private authService: AuthService,
        private router: Router,
        private ngZone: NgZone
    ) {
        this.broadcastChannel = new BroadcastChannel('sgaf2_inactivity_channel');
        this.setupBroadcastListener();
    }

    /**
     * Start monitoring inactivity. Call this when the user logs in or app initializes.
     */
    public startMonitoring() {
        if (this.timerSubscription) return; // Already monitoring

        this.lastActivity = Date.now();
        this.setupActivityEvents();
        this.startTimer();
    }

    /**
     * Stop monitoring. Call this on logout.
     */
    public stopMonitoring() {
        this.timerSubscription?.unsubscribe();
        this.eventsSubscription?.unsubscribe();
        this.timerSubscription = undefined;
        this.eventsSubscription = undefined;
        this.isWarning$.next(false);
    }

    /**
     * User explicitly requested to keep session active.
     */
    public keepActive() {
        this.lastActivity = Date.now();
        this.isWarning$.next(false);
        this.broadcastChannel.postMessage({ type: 'ACTIVITY_RESET' });
    }

    /**
     * Force logout from this or other tabs.
     */
    public logout() {
        this.stopMonitoring();
        this.authService.logout();
        this.broadcastChannel.postMessage({ type: 'LOGOUT' });
    }

    private setupActivityEvents() {
        // Events that constitute "activity"
        const events = ['mousemove', 'click', 'keypress', 'scroll', 'touchstart'];
        const eventStreams = events.map(ev => fromEvent(document, ev));

        // Merge all streams and throttle to avoid excessive processing
        const allEvents = merge(...eventStreams).pipe(
            throttleTime(1000) // Limit to once per second
        );

        this.eventsSubscription = allEvents.subscribe(() => {
            // Only reset if we are NOT in warning mode.
            // If in warning mode, user must explicitly click "Keep Session Active"
            if (!this.isWarning$.value) {
                this.lastActivity = Date.now();
                // Optional: Sync activity across tabs to keep them all alive
                // this.broadcastChannel.postMessage({ type: 'ACTIVITY_ALIVE' });
            }
        });
    }

    private startTimer() {
        // Run outside Angular zone to prevent excessive change detection cycles
        this.ngZone.runOutsideAngular(() => {
            this.timerSubscription = timer(0, 1000).subscribe(() => {
                const now = Date.now();
                const secondsInactive = Math.floor((now - this.lastActivity) / 1000);
                const secondsRemaining = this.TIMEOUT_SECONDS - secondsInactive;

                this.ngZone.run(() => {
                    this.checkStatus(secondsRemaining);
                });
            });
        });
    }

    private checkStatus(secondsRemaining: number) {
        if (secondsRemaining <= 0) {
            this.logout();
            this.isExpired$.next();
        } else if (secondsRemaining <= this.WARNING_SECONDS) {
            if (!this.isWarning$.value) {
                this.isWarning$.next(true);
            }
            this.countdown$.next(secondsRemaining);
        } else {
            if (this.isWarning$.value) {
                this.isWarning$.next(false);
            }
        }
    }

    private setupBroadcastListener() {
        this.broadcastChannel.onmessage = (event) => {
            if (event.data.type === 'LOGOUT') {
                this.stopMonitoring();
                this.authService.logout();
            } else if (event.data.type === 'ACTIVITY_RESET') {
                this.lastActivity = Date.now();
                this.isWarning$.next(false);
            }
        };
    }
}
