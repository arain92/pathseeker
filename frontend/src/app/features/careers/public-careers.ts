import { Component } from '@angular/core';
import { PublicShell } from '../../layout/public-shell/public-shell';
import { CareerBank } from './career-bank';
@Component({selector:'app-public-careers',imports:[PublicShell,CareerBank],template:`<app-public-shell><main id="main-content"><app-career-bank/></main></app-public-shell>`})
export class PublicCareers{}
