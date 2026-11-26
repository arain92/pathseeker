import { TestBed } from "@angular/core/testing";
import { provideHttpClient } from "@angular/common/http";
import { HttpTestingController, provideHttpClientTesting } from "@angular/common/http/testing";
import { Profile } from "./profile";

describe("Profile", () => {
  it("loads and displays the authenticated Career Passport", () => {
    TestBed.configureTestingModule({imports:[Profile],providers:[provideHttpClient(),provideHttpClientTesting()]});
    const fixture=TestBed.createComponent(Profile);
    const http=TestBed.inject(HttpTestingController);
    fixture.detectChanges();
    http.expectOne("/api/profile").flush({success:true,message:"ok",data:{name:"Test User",email:"test@example.com",role:"student",profile:{country:"Pakistan",education_level:"College",current_field:"Science",degree:null,field_of_study:null,graduation_year:null,job_title:null,years_experience:null,industry:null,career_goal:"Build useful products",transition_goal:null,resume_path:null},skills:[{id:1,name:"Research",proficiency:4}],interests:[{id:2,name:"Technology"}],completion:{percentage:67,missing:[]}}});
    http.expectOne("/api/profile-options").flush({success:true,message:"ok",data:{skills:[{id:1,name:"Research"}],interests:[{id:2,name:"Technology"}]}});
    fixture.detectChanges();
    expect((fixture.nativeElement as HTMLElement).querySelector("h1")?.textContent).toContain("Test User");
    expect((fixture.nativeElement as HTMLElement).querySelector(".skeleton")).toBeNull();
    http.verify();
  });
});
