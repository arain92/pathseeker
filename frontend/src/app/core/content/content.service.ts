import { HttpClient,HttpParams } from '@angular/common/http';
import { Injectable,inject } from '@angular/core';
import { Observable,map } from 'rxjs';
import { ApiResponse } from '../auth/auth.models';
import { ContentPage,LibraryResource,MediaItem,Story,StoryInput } from './content.models';
@Injectable({providedIn:'root'})
export class ContentService{
 private readonly http=inject(HttpClient);private readonly api='/api';
 resources(search=''):Observable<ContentPage<LibraryResource>>{return this.http.get<ContentPage<LibraryResource>>(`${this.api}/resources`,{params:this.params(search)});}
 media(search=''):Observable<ContentPage<MediaItem>>{return this.http.get<ContentPage<MediaItem>>(`${this.api}/multimedia`,{params:this.params(search)});}
 stories(search='',domain=''):Observable<ContentPage<Story>>{let params=this.params(search);if(domain)params=params.set('domain',domain);return this.http.get<ContentPage<Story>>(`${this.api}/success-stories`,{params});}
 resource(slug:string):Observable<LibraryResource>{return this.http.get<ApiResponse<LibraryResource>>(`${this.api}/resources/${slug}`).pipe(map(r=>r.data));}
 mediaItem(slug:string):Observable<MediaItem>{return this.http.get<ApiResponse<MediaItem>>(`${this.api}/multimedia/${slug}`).pipe(map(r=>r.data));}
 story(slug:string):Observable<Story>{return this.http.get<ApiResponse<Story>>(`${this.api}/success-stories/${slug}`).pipe(map(r=>r.data));}
 download(slug:string):Observable<string>{return this.http.post<ApiResponse<{url:string}>>(`${this.api}/resources/${slug}/download`,{}).pipe(map(r=>r.data.url));}
 rate(id:number,rating:number):Observable<{average:number;count:number;user_rating:number}>{return this.http.post<ApiResponse<{average:number;count:number;user_rating:number}>>(`${this.api}/multimedia/${id}/rating`,{rating}).pipe(map(r=>r.data));}
 submitStory(input:StoryInput):Observable<Story>{return this.http.post<ApiResponse<Story>>(`${this.api}/success-story-submissions`,input).pipe(map(r=>r.data));}
 myStories():Observable<Story[]>{return this.http.get<ApiResponse<Story[]>>(`${this.api}/success-story-submissions`).pipe(map(r=>r.data));}
 moderation(status='pending'):Observable<Story[]>{return this.http.get<ApiResponse<{data:Story[]}>>(`${this.api}/admin/success-stories`,{params:new HttpParams().set('status',status)}).pipe(map(r=>r.data.data));}
 moderate(id:number,status:'approved'|'rejected',moderation_notes:string|null):Observable<Story>{return this.http.patch<ApiResponse<Story>>(`${this.api}/admin/success-stories/${id}`,{status,moderation_notes}).pipe(map(r=>r.data));}
 private params(search:string):HttpParams{return search?new HttpParams().set('search',search):new HttpParams();}
}
