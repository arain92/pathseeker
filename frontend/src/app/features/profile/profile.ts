import { Component, OnInit, inject, signal } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { finalize, forkJoin } from 'rxjs';
import { Passport, ProfileOptions } from '../../core/profile/profile.models';
import { UserRole } from '../../core/auth/auth.models';
import { ProfileService } from '../../core/profile/profile.service';

@Component({selector:'app-profile',imports:[ReactiveFormsModule],templateUrl:'./profile.html',styleUrl:'./profile.scss'})
export class Profile implements OnInit{
  private readonly service=inject(ProfileService); protected readonly passport=signal<Passport|null>(null);protected readonly options=signal<ProfileOptions>({skills:[],interests:[]});protected readonly loading=signal(true);protected readonly saving=signal(false);protected readonly message=signal('');protected readonly error=signal('');protected readonly skills=signal(new Set<number>());protected readonly interests=signal(new Set<number>());
  protected readonly resumeMessage=signal('');
  protected readonly proficiency=signal<Record<number,number>>({});
  protected readonly form=new FormGroup({name:new FormControl('',{nonNullable:true,validators:[Validators.required]}),role:new FormControl<UserRole>('student',{nonNullable:true}),country:new FormControl(''),education_level:new FormControl(''),current_field:new FormControl(''),degree:new FormControl(''),field_of_study:new FormControl(''),graduation_year:new FormControl<number|null>(null),job_title:new FormControl(''),years_experience:new FormControl<number|null>(null),industry:new FormControl(''),career_goal:new FormControl(''),transition_goal:new FormControl(''),resume_path:new FormControl('')});

  ngOnInit():void{
    forkJoin({passport:this.service.load(),options:this.service.options()})
      .pipe(finalize(()=>this.loading.set(false)))
      .subscribe({
        next:({passport,options})=>{
          const data=passport.data;
          this.passport.set(data);
          this.options.set(options.data);
          this.form.patchValue({
            name:data.name,
            role:data.role,
            country:data.profile?.country??'',
            education_level:data.profile?.education_level??'',
            current_field:data.profile?.current_field??'',
            degree:data.profile?.degree??'',
            field_of_study:data.profile?.field_of_study??'',
            graduation_year:data.profile?.graduation_year??null,
            job_title:data.profile?.job_title??'',
            years_experience:data.profile?.years_experience??null,
            industry:data.profile?.industry??'',
            career_goal:data.profile?.career_goal??'',
            transition_goal:data.profile?.transition_goal??'',
            resume_path:data.profile?.resume_path??'',
          });
          this.skills.set(new Set(data.skills.map(skill=>skill.id)));
          this.interests.set(new Set(data.interests.map(interest=>interest.id)));
          this.proficiency.set(Object.fromEntries(data.skills.map(skill=>[skill.id,skill.proficiency])));
        },
        error:error=>this.error.set(error.error?.message??'We could not load your Career Passport. Please refresh and try again.'),
      });
  }
  protected toggle(kind:'skills'|'interests',id:number):void{const source=kind==='skills'?this.skills:this.interests;source.update(current=>{const next=new Set(current);next.has(id)?next.delete(id):next.add(id);return next;});}
  protected uploadResume(event:Event):void{const file=(event.target as HTMLInputElement).files?.[0];if(!file)return;this.resumeMessage.set('Uploading…');this.service.uploadResume(file).subscribe({next:r=>{this.form.controls.resume_path.setValue(r.data.resume_path);this.resumeMessage.set('Resume uploaded and saved.');},error:()=>this.resumeMessage.set('Upload failed. Use a PDF, DOC, or DOCX file up to 5 MB.')});}
  protected setProficiency(id:number,value:string):void{this.proficiency.update(current=>({...current,[id]:Number(value)}));}protected save():void{if(this.form.invalid)return;this.saving.set(true);this.error.set('');this.message.set('');const payload:{role?:UserRole;[key:string]:unknown}={...this.form.getRawValue(),skills:[...this.skills()].map(id=>({id,proficiency:this.proficiency()[id]??3})),interest_ids:[...this.interests()]};if(this.passport()?.role==='admin')delete payload.role;this.service.save(payload).pipe(finalize(()=>this.saving.set(false))).subscribe({next:r=>{this.passport.set(r.data);this.message.set(r.message);},error:e=>this.error.set(e.error?.message??'We could not save your Career Passport. Review the highlighted information.')});}
}
