import { HttpClient } from '@angular/common/http';
import { Injectable, computed, inject, signal } from '@angular/core';
import { Observable, tap } from 'rxjs';
import { ApiResponse, AuthPayload, User, UserRole } from './auth.models';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly http=inject(HttpClient); private readonly api='/api/auth';
  private readonly currentUser=signal<User|null>(null);
  readonly user=this.currentUser.asReadonly(); readonly authenticated=computed(()=>this.currentUser()!==null);
  login(input:{email:string;password:string}):Observable<ApiResponse<AuthPayload>>{return this.http.post<ApiResponse<AuthPayload>>(`${this.api}/login`,input).pipe(tap(r=>this.accept(r.data)));}
  register(input:{name:string;email:string;password:string;password_confirmation:string;role:Exclude<UserRole,'admin'>}):Observable<ApiResponse<AuthPayload>>{return this.http.post<ApiResponse<AuthPayload>>(`${this.api}/register`,input).pipe(tap(r=>this.accept(r.data)));}
  restore():Observable<ApiResponse<User>>{return this.http.get<ApiResponse<User>>(`${this.api}/me`).pipe(tap(r=>this.currentUser.set(r.data)));}
  forgotPassword(email:string):Observable<ApiResponse<null>>{return this.http.post<ApiResponse<null>>(`${this.api}/forgot-password`,{email});}
  resetPassword(input:{email:string;token:string;password:string;password_confirmation:string}):Observable<ApiResponse<null>>{return this.http.post<ApiResponse<null>>(`${this.api}/reset-password`,input);}
  logout():Observable<ApiResponse<null>>{return this.http.post<ApiResponse<null>>(`${this.api}/logout`,{}).pipe(tap(()=>{localStorage.removeItem('pathseeker_token');this.currentUser.set(null);}));}
  private accept(data:AuthPayload):void{localStorage.setItem('pathseeker_token',data.token);this.currentUser.set(data.user);}
}
