import { bootstrapApplication } from '@angular/platform-browser';
import { appConfig } from './app/app.config';
import { App } from './app/app';

bootstrapApplication(App, appConfig).then(() => {
  if ('serviceWorker' in navigator) {
    const local=['localhost','127.0.0.1'].includes(location.hostname);
    if (local) navigator.serviceWorker.getRegistrations().then(registrations => registrations.forEach(registration => void registration.unregister()));
    else navigator.serviceWorker.register('/sw.js').catch(() => undefined);
  }
}).catch((err) => console.error(err));
