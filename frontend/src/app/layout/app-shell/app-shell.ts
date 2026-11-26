import { Component, inject } from '@angular/core';
import { Router, RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';
import { AuthService } from '../../core/auth/auth.service';
import { PreferencesControl } from '../../shared/preferences/preferences-control';
@Component({selector:'app-app-shell',imports:[RouterLink,RouterLinkActive,RouterOutlet,PreferencesControl],templateUrl:'./app-shell.html',styleUrl:'./shell.scss'})
export class AppShell{protected readonly auth=inject(AuthService);private readonly router=inject(Router);protected logout():void{this.auth.logout().subscribe(()=>void this.router.navigateByUrl('/'));}}
