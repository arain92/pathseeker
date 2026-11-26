import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { catchError, map, of } from 'rxjs';
import { AuthService } from '../auth/auth.service';

export const authGuard:CanActivateFn=()=>{
  const auth=inject(AuthService),router=inject(Router);
  if(!localStorage.getItem('pathseeker_token')) return router.createUrlTree(['/auth/login']);
  if(auth.user()) return true;
  return auth.restore().pipe(map(()=>true),catchError(()=>{localStorage.removeItem('pathseeker_token');return of(router.createUrlTree(['/auth/login']));}));
};

export const adminGuard:CanActivateFn=()=>{
  const auth=inject(AuthService),router=inject(Router);
  const decide=()=>auth.user()?.role==='admin'?true:router.createUrlTree(['/app']);
  if(!localStorage.getItem('pathseeker_token')) return router.createUrlTree(['/auth/login']);
  if(auth.user()) return decide();
  return auth.restore().pipe(map(decide),catchError(()=>of(router.createUrlTree(['/auth/login']))));
};
