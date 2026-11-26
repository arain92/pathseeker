export type UserRole = 'student' | 'graduate' | 'professional' | 'admin';
export interface UserProfile { country: string | null; education_level: string | null; career_goal: string | null; }
export interface User { id:number; name:string; email:string; role:UserRole; is_active:boolean; profile:UserProfile|null; }
export interface ApiResponse<T> { success:boolean; message:string; data:T; }
export interface AuthPayload { user:User; token:string; }
