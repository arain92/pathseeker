import { Component } from '@angular/core';
import { RouterLink } from '@angular/router';
import { PublicShell } from '../../../layout/public-shell/public-shell';

@Component({selector:'app-about',imports:[PublicShell,RouterLink],templateUrl:'./about.html',styleUrl:'./about.scss'})
export class About {}
