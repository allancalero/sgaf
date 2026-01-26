import { ApplicationConfig, provideBrowserGlobalErrorListeners, APP_INITIALIZER } from '@angular/core';
import { provideRouter, withHashLocation, withDebugTracing } from '@angular/router';
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { LOCALE_ID } from '@angular/core';
import { registerLocaleData } from '@angular/common';
import localeEsNi from '@angular/common/locales/es-NI';
import { authInterceptor } from './interceptors/auth.interceptor';
import { AuthService } from './services/auth.service';

registerLocaleData(localeEsNi);

import { routes } from './app.routes';

import { APP_BASE_HREF } from '@angular/common';

function initializeApp(authService: AuthService) {
  return () => authService.checkAuthStatus(); // Waits for Observable to complete
}

export const appConfig: ApplicationConfig = {
  providers: [
    provideBrowserGlobalErrorListeners(),
    provideRouter(routes, withHashLocation(), withDebugTracing()),
    provideHttpClient(withInterceptors([authInterceptor])),
    { provide: LOCALE_ID, useValue: 'es-NI' },
    {
      provide: APP_INITIALIZER,
      useFactory: initializeApp,
      deps: [AuthService],
      multi: true
    }
  ]
};
