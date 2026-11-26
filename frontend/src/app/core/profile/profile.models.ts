import { UserRole } from '../auth/auth.models';
export interface Option{id:number;name:string}
export interface PassportProfile{country:string|null;education_level:string|null;current_field:string|null;degree:string|null;field_of_study:string|null;graduation_year:number|null;job_title:string|null;years_experience:number|null;industry:string|null;career_goal:string|null;transition_goal:string|null;resume_path:string|null}
export interface PassportSkill extends Option{proficiency:number}
export interface Passport{name:string;email:string;role:UserRole;profile:PassportProfile;skills:PassportSkill[];interests:Option[];completion:{percentage:number;missing:string[]}}
export interface ProfileOptions{skills:Option[];interests:Option[]}
