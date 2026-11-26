import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { ApiResponse } from '../auth/auth.models';
import { DashboardData, RecentItem, SavedItem } from './personalization.models';

@Injectable({providedIn:'root'})
export class PersonalizationService {
  private readonly http=inject(HttpClient); private readonly api='/api';
  dashboard():Observable<DashboardData>{return this.http.get<ApiResponse<DashboardData>>(`${this.api}/dashboard`).pipe(map(r=>r.data));}
  saved(search=''):Observable<SavedItem[]>{const params=search?new HttpParams().set('search',search):undefined;return this.http.get<ApiResponse<SavedItem[]>>(`${this.api}/bookmarks`,{params}).pipe(map(r=>r.data));}
  saveCareer(id:number):Observable<SavedItem>{return this.http.post<ApiResponse<SavedItem>>(`${this.api}/bookmarks`,{type:'career',id}).pipe(map(r=>r.data));}
  saveItem(type:'resource'|'multimedia'|'story',id:number):Observable<SavedItem>{return this.http.post<ApiResponse<SavedItem>>(`${this.api}/bookmarks`,{type,id}).pipe(map(r=>r.data));}
  updateNote(id:number,note:string|null):Observable<SavedItem>{return this.http.patch<ApiResponse<SavedItem>>(`${this.api}/bookmarks/${id}`,{note}).pipe(map(r=>r.data));}
  remove(id:number):Observable<void>{return this.http.delete<ApiResponse<null>>(`${this.api}/bookmarks/${id}`).pipe(map(()=>undefined));}
  recordCareerView(id:number):Observable<RecentItem>{return this.http.post<ApiResponse<RecentItem>>(`${this.api}/recent-views`,{type:'career',id}).pipe(map(r=>r.data));}
  recordView(type:'resource'|'multimedia',id:number):Observable<RecentItem>{return this.http.post<ApiResponse<RecentItem>>(`${this.api}/recent-views`,{type,id}).pipe(map(r=>r.data));}
}
