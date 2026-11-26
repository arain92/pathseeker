import { Component, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { PublicShell } from '../../../layout/public-shell/public-shell';

@Component({selector:'app-home',imports:[PublicShell,RouterLink],templateUrl:'./home.html',styleUrl:'./home.scss'})
export class Home {
  protected readonly pointer=signal({x:50,y:45});
  protected readonly domains=['Technology','Healthcare','Business','Engineering','Arts & Design','Education','Finance','Science'];
  protected readonly steps=['Build your Career Passport','Take the Interest Quiz','Discover career matches','Explore career roadmaps','Save and build your journey'];
  protected track(event:PointerEvent):void{const target=event.currentTarget as HTMLElement;const box=target.getBoundingClientRect();this.pointer.set({x:(event.clientX-box.left)/box.width*100,y:(event.clientY-box.top)/box.height*100});}
}
