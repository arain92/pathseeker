import { Component, inject, signal } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { finalize } from 'rxjs';
import { Career } from '../../core/careers/career.models';
import { CareerService } from '../../core/careers/career.service';
import { AuthService } from '../../core/auth/auth.service';
import { PersonalizationService } from '../../core/personalization/personalization.service';
import { PublicShell } from '../../layout/public-shell/public-shell';
@Component({selector:'app-career-detail',imports:[PublicShell,RouterLink],templateUrl:'./career-detail.html',styleUrl:'./career-detail.scss'})
export class CareerDetail{private readonly service=inject(CareerService);private readonly personalization=inject(PersonalizationService);private readonly route=inject(ActivatedRoute);protected readonly auth=inject(AuthService);protected readonly career=signal<Career|null>(null);protected readonly loading=signal(true);protected readonly error=signal('');protected readonly saved=signal(false);protected readonly action=signal('');constructor(){this.service.get(this.route.snapshot.paramMap.get('slug')??'').pipe(finalize(()=>this.loading.set(false))).subscribe({next:data=>{this.career.set(data);if(this.auth.authenticated())this.personalization.recordCareerView(data.id).subscribe({error:()=>undefined});},error:()=>this.error.set('This career could not be found or is not published.')});}protected save(item:Career):void{if(!this.auth.authenticated())return;this.personalization.saveCareer(item.id).subscribe({next:()=>{this.saved.set(true);this.action.set('Saved to your Career Passport.');},error:()=>this.action.set('We couldn’t save this path. Please try again.')});}}
