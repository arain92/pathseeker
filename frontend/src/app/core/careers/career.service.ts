import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, forkJoin, map } from 'rxjs';
import { ApiResponse } from '../auth/auth.models';
import { Career, CareerFilterOptions, CareerPage, Domain } from './career.models';
@Injectable({providedIn:'root'})
export class CareerService{private readonly http=inject(HttpClient);private readonly api='/api';list(filters:Record<string,string|number|undefined>):Observable<CareerPage>{let params=new HttpParams();Object.entries(filters).forEach(([key,value])=>{if(value!==undefined&&value!=='')params=params.set(key,String(value));});return this.http.get<CareerPage>(`${this.api}/careers`,{params});}domains():Observable<Domain[]>{return this.http.get<ApiResponse<Domain[]>>(`${this.api}/career-domains`).pipe(map(r=>r.data));}options():Observable<CareerFilterOptions>{return this.http.get<ApiResponse<CareerFilterOptions>>(`${this.api}/career-options`).pipe(map(r=>r.data));}get(slug:string):Observable<Career>{return this.http.get<ApiResponse<Career>>(`${this.api}/careers/${slug}`).pipe(map(r=>r.data));}compare(slugs:string[]):Observable<Career[]>{return forkJoin(slugs.map(slug=>this.get(slug)));}}
