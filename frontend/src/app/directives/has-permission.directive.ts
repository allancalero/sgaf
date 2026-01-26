import { Directive, Input, TemplateRef, ViewContainerRef, OnInit, OnDestroy } from '@angular/core';
import { AuthService } from '../services/auth.service';
import { Subscription } from 'rxjs';

@Directive({
    selector: '[appHasPermission]',
    standalone: true
})
export class HasPermissionDirective implements OnInit, OnDestroy {
    @Input('appHasPermission') requiredPermission: string = '';
    private userSub: Subscription | undefined;
    private hasView = false;

    constructor(
        private templateRef: TemplateRef<any>,
        private viewContainer: ViewContainerRef,
        private authService: AuthService
    ) { }

    ngOnInit() {
        this.userSub = this.authService.currentUser.subscribe(user => {
            if (!user || !user.permissions) {
                this.viewContainer.clear();
                this.hasView = false;
                return;
            }

            // Check if user has permission
            const hasPermission = user.permissions.includes(this.requiredPermission) || user.role === 'admin'; // Admin gets all? Or explicit? Assuming Admin role might bypass or has all permissions.
            // Better: Just check permissions array. Backend should assign all perms to Admin.

            if (hasPermission && !this.hasView) {
                this.viewContainer.createEmbeddedView(this.templateRef);
                this.hasView = true;
            } else if (!hasPermission && this.hasView) {
                this.viewContainer.clear();
                this.hasView = false;
            }
        });
    }

    ngOnDestroy() {
        if (this.userSub) {
            this.userSub.unsubscribe();
        }
    }
}
