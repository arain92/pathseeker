export interface Domain{id:number;name:string;slug:string;description:string;careers_count:number}
export interface CareerSkill{id:number;name:string;importance:'required'|'recommended';weight:number}
export interface Salary{minimum:number|null;maximum:number|null;currency:string;context:string|null}
export interface Career{id:number;title:string;slug:string;summary:string;description?:string;domain:Pick<Domain,'id'|'name'|'slug'>;education_level:string;experience_level:string;salary:Salary;demand_level:string;future_outlook:string|null;icon:string|null;responsibilities?:string[];education_pathway?:string[];skills:CareerSkill[]}
export interface CareerPage{success:boolean;message:string;data:Career[];meta:{current_page:number;last_page:number;per_page:number;total:number}}

export interface CareerFilterOptions{skills:{id:number;name:string}[];education_levels:string[];experience_levels:string[]}
