import { Component, inject } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { PublicShell } from '../../../layout/public-shell/public-shell';
@Component({selector:'app-info',imports:[PublicShell,RouterLink],template:`<app-public-shell><main id="main-content" class="container page"><p class="eyebrow">PathSeeker</p><h1>{{title}}</h1><p>{{copy}}</p><a class="button button--primary" routerLink="/">Return home</a></main></app-public-shell>`,styles:[`.page{min-height:65vh;padding-block:8rem}.page h1{font-size:clamp(2.7rem,7vw,5rem);letter-spacing:-.055em;margin:.5rem 0}.page p:not(.eyebrow){max-width:720px;color:var(--text-secondary);font-size:1.15rem;margin-bottom:2rem}`]})
export class Info{private readonly route=inject(ActivatedRoute);protected readonly title=this.route.snapshot.data['title'] as string;protected readonly copy=this.route.snapshot.data['copy'] as string;}
