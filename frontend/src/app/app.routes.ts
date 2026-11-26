import { Routes } from '@angular/router';
import { adminGuard, authGuard } from './core/guards/auth.guard';

export const routes: Routes = [
  { path: '', loadComponent: () => import('./features/public/home/home').then(m => m.Home), title: 'PathSeeker — Discover What Fits You Best' },
  { path: 'careers', loadComponent: () => import('./features/careers/public-careers').then(m => m.PublicCareers), title: 'Career Bank — PathSeeker' },
  { path: 'careers/:slug', loadComponent: () => import('./features/careers/career-detail').then(m => m.CareerDetail), title: 'Career Path — PathSeeker' },
  { path: 'compare', loadComponent: () => import('./features/careers/compare').then(m => m.Compare), title: 'Compare Careers — PathSeeker' },
  { path: 'resources', loadComponent: () => import('./features/content/content-hub').then(m => m.ContentHub), data:{kind:'resources'}, title: 'Resources — PathSeeker' },
  { path: 'resources/:slug', loadComponent: () => import('./features/content/content-detail').then(m => m.ContentDetail), data:{kind:'resource'}, title: 'Resource — PathSeeker' },
  { path: 'multimedia', loadComponent: () => import('./features/content/content-hub').then(m => m.ContentHub), data:{kind:'multimedia'}, title: 'Multimedia — PathSeeker' },
  { path: 'multimedia/:slug', loadComponent: () => import('./features/content/content-detail').then(m => m.ContentDetail), data:{kind:'multimedia'}, title: 'Multimedia — PathSeeker' },
  { path: 'stories', loadComponent: () => import('./features/content/content-hub').then(m => m.ContentHub), data:{kind:'stories'}, title: 'Success Stories — PathSeeker' },
  { path: 'stories/:slug', loadComponent: () => import('./features/content/content-detail').then(m => m.ContentDetail), data:{kind:'story'}, title: 'Success Story — PathSeeker' },
  { path: 'about', loadComponent: () => import('./features/public/info/about').then(m => m.About), title: 'About — PathSeeker' },
  { path: 'auth/login', loadComponent: () => import('./features/auth/login').then(m => m.Login), title: 'Sign in — PathSeeker' },
  { path: 'auth/register', loadComponent: () => import('./features/auth/register').then(m => m.Register), title: 'Create your Career Passport — PathSeeker' },
  { path: 'auth/forgot-password', loadComponent: () => import('./features/auth/recovery').then(m => m.Recovery), title: 'Account recovery — PathSeeker' },
  { path: 'auth/reset-password', loadComponent: () => import('./features/auth/recovery').then(m => m.Recovery), title: 'Reset password — PathSeeker' },
  { path:'app', canActivate:[authGuard], loadComponent:()=>import('./layout/app-shell/app-shell').then(m=>m.AppShell), children:[
    {path:'',loadComponent:()=>import('./features/dashboard/overview').then(m=>m.Overview),title:'Overview — PathSeeker'},
    {path:'profile',loadComponent:()=>import('./features/profile/profile').then(m=>m.Profile),title:'Career Passport — PathSeeker'},
    {path:'discover',loadComponent:()=>import('./features/careers/career-bank').then(m=>m.CareerBank),title:'Discover Careers — PathSeeker'},
    {path:'quiz',loadComponent:()=>import('./features/quiz/quiz').then(m=>m.Quiz),title:'Career Quiz — PathSeeker'},
    {path:'quiz/results/:id',loadComponent:()=>import('./features/quiz/quiz-result').then(m=>m.QuizResultReport),title:'Career DNA — PathSeeker'},
    {path:'saved',loadComponent:()=>import('./features/saved/saved').then(m=>m.Saved),title:'Saved paths — PathSeeker'},
    {path:'stories/submit',loadComponent:()=>import('./features/content/story-submission').then(m=>m.StorySubmission),title:'Share your story — PathSeeker'},
    {path:'feedback',loadComponent:()=>import('./features/communication/feedback').then(m=>m.Feedback),title:'Feedback — PathSeeker'},
    {path:'notifications',loadComponent:()=>import('./features/communication/notifications').then(m=>m.Notifications),title:'Notifications — PathSeeker'},
    {path:'**',loadComponent:()=>import('./features/public/error-page').then(m=>m.ErrorPage),data:{code:404,heading:'This application path doesn’t exist.'}}
  ]},
  { path:'admin', canActivate:[adminGuard], loadComponent:()=>import('./layout/admin-shell/admin-shell').then(m=>m.AdminShell), children:[
    {path:'',loadComponent:()=>import('./features/admin/admin-overview').then(m=>m.AdminOverview),title:'Admin — PathSeeker'},
    {path:'profile',loadComponent:()=>import('./features/profile/profile').then(m=>m.Profile),title:'Admin profile — PathSeeker'},
    {path:'users',loadComponent:()=>import('./features/admin/user-management').then(m=>m.UserManagement),title:'User management — PathSeeker'},
    {path:'careers',loadComponent:()=>import('./features/admin/career-management').then(m=>m.CareerManagement),title:'Career management — PathSeeker'},
    {path:'quiz',loadComponent:()=>import('./features/admin/quiz-management').then(m=>m.QuizManagement),title:'Quiz management — PathSeeker'},
    {path:'resources',loadComponent:()=>import('./features/admin/content-management').then(m=>m.ContentManagement),data:{mode:'resources'},title:'Resource management — PathSeeker'},
    {path:'media',loadComponent:()=>import('./features/admin/content-management').then(m=>m.ContentManagement),data:{mode:'multimedia'},title:'Multimedia management — PathSeeker'},
    {path:'stories',loadComponent:()=>import('./features/admin/story-moderation').then(m=>m.StoryModeration),title:'Story moderation — PathSeeker'},
    {path:'feedback',loadComponent:()=>import('./features/admin/communication-admin').then(m=>m.CommunicationAdmin),data:{mode:'feedback'},title:'Feedback operations — PathSeeker'},
    {path:'announcements',loadComponent:()=>import('./features/admin/communication-admin').then(m=>m.CommunicationAdmin),data:{mode:'announcements'},title:'Announcements — PathSeeker'},
    {path:'**',loadComponent:()=>import('./features/public/error-page').then(m=>m.ErrorPage),data:{code:404,heading:'This admin path doesn’t exist.'}}
  ]},
  { path: 'forbidden', loadComponent:()=>import('./features/public/error-page').then(m=>m.ErrorPage),data:{code:403,heading:'You don’t have access to this area.',copy:'Sign in with an account that has permission, or return to public career discovery.'},title:'Access denied — PathSeeker' },
  { path: '**', loadComponent:()=>import('./features/public/error-page').then(m=>m.ErrorPage),data:{code:404},title:'Page not found — PathSeeker' },
];
