import { DOCUMENT } from '@angular/common';
import { Injectable, inject, signal } from '@angular/core';

export type ThemePreference = 'light' | 'dark' | 'system';
export type FontPreference = 'default' | 'larger' | 'largest';

@Injectable({ providedIn: 'root' })
export class PreferencesService {
  private readonly document = inject(DOCUMENT);
  readonly theme = signal<ThemePreference>(this.readTheme());
  readonly font = signal<FontPreference>(this.readFont());

  constructor() { this.applyTheme(this.theme()); this.applyFont(this.font()); }

  setTheme(value: ThemePreference): void { this.theme.set(value); localStorage.setItem('pathseeker_theme', value); this.applyTheme(value); }
  setFont(value: FontPreference): void { this.font.set(value); localStorage.setItem('pathseeker_font', value); this.applyFont(value); }

  private applyTheme(value: ThemePreference): void {
    const resolved = value === 'system' ? (window.matchMedia?.('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : value;
    this.document.documentElement.dataset['theme'] = resolved;
    this.document.documentElement.setAttribute('data-theme-preference', value);
    this.document.documentElement.style.colorScheme = resolved;
  }
  private applyFont(value: FontPreference): void { const scale = value === 'largest' ? '1.2' : value === 'larger' ? '1.1' : '1'; this.document.documentElement.style.setProperty('--font-scale', scale); }
  private readTheme(): ThemePreference { const value = localStorage.getItem('pathseeker_theme'); return value === 'dark' || value === 'system' ? value : 'light'; }
  private readFont(): FontPreference { const value = localStorage.getItem('pathseeker_font'); return value === 'larger' || value === 'largest' ? value : 'default'; }
}
