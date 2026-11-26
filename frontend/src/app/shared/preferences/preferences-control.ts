import { Component, inject } from '@angular/core';
import { FontPreference, PreferencesService, ThemePreference } from '../../core/preferences/preferences.service';

@Component({
  selector: 'app-preferences-control',
  template: `<div class="preferences" aria-label="Display preferences">
    <label>Theme
      <select [value]="preferences.theme()" (change)="theme($event)"><option value="light">Light</option><option value="dark">Dark</option><option value="system">System</option></select>
    </label>
    <label>Text size
      <select [value]="preferences.font()" (change)="font($event)"><option value="default">Default</option><option value="larger">Larger</option><option value="largest">Largest</option></select>
    </label>
  </div>`,
  styles: [`.preferences{display:flex;flex-wrap:wrap;gap:.55rem}.preferences label{display:grid;gap:.15rem;color:var(--text-secondary);font-size:.62rem;font-weight:750}.preferences select{min-height:38px;padding:.2rem .45rem;border:1px solid var(--border);border-radius:.45rem;background:var(--surface);color:var(--text-primary);font-size:.72rem}`]
})
export class PreferencesControl {
  protected readonly preferences = inject(PreferencesService);
  protected theme(event: Event): void { this.preferences.setTheme((event.target as HTMLSelectElement).value as ThemePreference); }
  protected font(event: Event): void { this.preferences.setFont((event.target as HTMLSelectElement).value as FontPreference); }
}
