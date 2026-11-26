import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiResponse } from '../auth/auth.models';
import { Passport, ProfileOptions } from './profile.models';
@Injectable({providedIn:'root'})
export class ProfileService{private readonly http=inject(HttpClient);private readonly api='/api';load():Observable<ApiResponse<Passport>>{return this.http.get<ApiResponse<Passport>>(`${this.api}/profile`);}options():Observable<ApiResponse<ProfileOptions>>{return this.http.get<ApiResponse<ProfileOptions>>(`${this.api}/profile-options`);}save(payload:Record<string,unknown>):Observable<ApiResponse<Passport>>{return this.http.put<ApiResponse<Passport>>(`${this.api}/profile`,payload);}uploadResume(file:File):Observable<ApiResponse<{resume_path:string}>>{const body=new FormData();body.append('resume',file);return this.http.post<ApiResponse<{resume_path:string}>>(`${this.api}/profile/resume`,body);}}
