import { Component, OnInit, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { finalize } from 'rxjs';
import { PersonalizationService } from '../../core/personalization/personalization.service';
import { SavedItem } from '../../core/personalization/personalization.models';

@Component({selector:'app-saved',imports:[FormsModule,RouterLink],templateUrl:'./saved.html',styleUrl:'./saved.scss'})
export class Saved implements OnInit {
  private readonly service=inject(PersonalizationService);
  protected readonly items=signal<SavedItem[]>([]); protected readonly loading=signal(true); protected readonly message=signal(''); protected search='';
  ngOnInit():void{this.load();}
  protected load():void{this.loading.set(true);this.service.saved(this.search).pipe(finalize(()=>this.loading.set(false))).subscribe({next:items=>this.items.set(items),error:()=>this.message.set('We couldn’t load your saved paths. Please try again.')});}
  protected saveNote(item:SavedItem,note:string):void{this.service.updateNote(item.id,note.trim()||null).subscribe({next:updated=>{this.replace(updated);this.message.set('Private note saved.');},error:()=>this.message.set('We couldn’t save that note.')});}
  protected remove(item:SavedItem):void{this.service.remove(item.id).subscribe({next:()=>{this.items.update(items=>items.filter(value=>value.id!==item.id));this.message.set('Saved path removed.');},error:()=>this.message.set('We couldn’t remove that path.')});}
  protected title(saved:SavedItem):string{return 'title' in saved.item?saved.item.title:saved.item.person_name;}
  protected summary(saved:SavedItem):string{return 'summary' in saved.item?saved.item.summary:saved.item.description;}
  protected context(saved:SavedItem):string{if(saved.type==='career'&&'domain' in saved.item&&typeof saved.item.domain==='object')return saved.item.domain.name;if(saved.type==='story'&&'career_title' in saved.item)return saved.item.career_title;return 'category' in saved.item?saved.item.category:saved.type;}
  protected link(saved:SavedItem):string[]{return saved.type==='career'&&'slug' in saved.item?['/careers',saved.item.slug]:[saved.type==='resource'?'/resources':saved.type==='multimedia'?'/multimedia':'/stories'];}
  protected exportPdf():void{window.print();}
  protected async share():Promise<void>{const text=this.items().map(item=>`${this.title(item)}${item.note?` — ${item.note}`:''}`).join('\n');if(navigator.share){await navigator.share({title:'My PathSeeker bookmarks',text,url:location.href});return;}location.href=`mailto:?subject=${encodeURIComponent('My PathSeeker bookmarks')}&body=${encodeURIComponent(text)}`;}
  private replace(updated:SavedItem):void{this.items.update(items=>items.map(item=>item.id===updated.id?updated:item));}
}
