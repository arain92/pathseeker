import { Component, inject, signal } from '@angular/core';
import { DomSanitizer, SafeResourceUrl } from '@angular/platform-browser';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { finalize, Observable } from 'rxjs';
import { AuthService } from '../../core/auth/auth.service';
import { ContentService } from '../../core/content/content.service';
import { LibraryResource, MediaItem, Story } from '../../core/content/content.models';
import { PersonalizationService } from '../../core/personalization/personalization.service';
import { PublicShell } from '../../layout/public-shell/public-shell';

type DetailKind='resource'|'multimedia'|'story';
type DetailItem=LibraryResource|MediaItem|Story;
@Component({selector:'app-content-detail',imports:[PublicShell,RouterLink],templateUrl:'./content-detail.html',styleUrl:'./content-detail.scss'})
export class ContentDetail {
  private readonly content=inject(ContentService);private readonly personalization=inject(PersonalizationService);private readonly route=inject(ActivatedRoute);private readonly sanitizer=inject(DomSanitizer);
  protected readonly auth=inject(AuthService);protected readonly kind=this.route.snapshot.data['kind'] as DetailKind;protected readonly item=signal<DetailItem|null>(null);protected readonly loading=signal(true);protected readonly error=signal('');protected readonly action=signal('');protected readonly transcriptOpen=signal(false);protected readonly previewOpen=signal(false);
  constructor(){const slug=this.route.snapshot.paramMap.get('slug')??'';const request=(this.kind==='resource'?this.content.resource(slug):this.kind==='multimedia'?this.content.mediaItem(slug):this.content.story(slug)) as Observable<DetailItem>;request.pipe(finalize(()=>this.loading.set(false))).subscribe({next:item=>{this.item.set(item);if(this.auth.authenticated()&&this.kind!=='story')this.personalization.recordView(this.kind,item.id).subscribe({error:()=>undefined});},error:()=>this.error.set('This published item could not be found.')});}
  protected resource(item:DetailItem):LibraryResource{return item as LibraryResource;}protected media(item:DetailItem):MediaItem{return item as MediaItem;}protected story(item:DetailItem):Story{return item as Story;}
  protected safeUrl(url:string):SafeResourceUrl{return this.sanitizer.bypassSecurityTrustResourceUrl(url);}
  protected toggleTranscript():void{this.transcriptOpen.update(value=>!value);}
  protected togglePreview():void{this.previewOpen.update(value=>!value);}
  protected save(item:DetailItem):void{if(!this.auth.authenticated()){this.action.set('Sign in to save this item.');return;}this.personalization.saveItem(this.kind,item.id).subscribe({next:()=>this.action.set('Saved to your Career Passport.'),error:()=>this.action.set('We couldn’t save this item.')});}
  protected download(item:LibraryResource):void{this.content.download(item.slug).subscribe({next:url=>window.open(url,'_blank','noopener'),error:()=>this.action.set('We couldn’t open that resource.')});}
  protected rate(item:MediaItem,rating:number):void{if(!this.auth.authenticated()){this.action.set('Sign in to rate this content.');return;}this.content.rate(item.id,rating).subscribe({next:stats=>{item.ratings_avg_rating=stats.average;item.ratings_count=stats.count;this.action.set(`Your ${rating}-star rating was saved.`);},error:()=>this.action.set('We couldn’t save your rating.')});}
}
