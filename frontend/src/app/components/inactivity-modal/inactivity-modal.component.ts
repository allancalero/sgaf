
import { Component, OnDestroy, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { InactivityService } from '../../services/inactivity.service';
import { Subject, takeUntil } from 'rxjs';

@Component({
    selector: 'app-inactivity-modal',
    standalone: true,
    imports: [CommonModule],
    templateUrl: './inactivity-modal.component.html',
    styles: []
})
export class InactivityModalComponent implements OnInit, OnDestroy {
    showModal = false;
    countdown = 0;
    private destroy$ = new Subject<void>();

    constructor(private inactivityService: InactivityService) { }

    ngOnInit(): void {
        // Check if we start in a warning state
        this.inactivityService.isWarning$
            .pipe(takeUntil(this.destroy$))
            .subscribe(isWarning => {
                this.showModal = isWarning;
            });

        // Update countdown
        this.inactivityService.countdown$
            .pipe(takeUntil(this.destroy$))
            .subscribe(seconds => {
                this.countdown = seconds;
            });
    }

    ngOnDestroy(): void {
        this.destroy$.next();
        this.destroy$.complete();
    }

    keepActive() {
        this.inactivityService.keepActive();
    }

    logout() {
        this.inactivityService.logout();
    }
}
