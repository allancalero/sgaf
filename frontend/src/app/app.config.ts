import { ApplicationConfig, provideBrowserGlobalErrorListeners } from '@angular/core';
import { provideRouter, withHashLocation } from '@angular/router';
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { LOCALE_ID } from '@angular/core';
import { registerLocaleData } from '@angular/common';
import localeEsNi from '@angular/common/locales/es-NI';
import { authInterceptor } from './interceptors/auth.interceptor';

registerLocaleData(localeEsNi);

import { routes } from './app.routes';

import { APP_BASE_HREF } from '@angular/common';

export const appConfig: ApplicationConfig = {
  providers: [
    provideBrowserGlobalErrorListeners(),
    provideRouter(routes),
    provideHttpClient(withInterceptors([authInterceptor])),
    { provide: LOCALE_ID, useValue: 'es-NI' },
    { provide: APP_BASE_HREF, useValue: '/SGAF2/' }
  ]
};
