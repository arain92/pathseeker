export interface Feedback{id:number;type:'bug'|'suggestion'|'question'|'other';category:string;subject:string;message:string;status:'new'|'reviewing'|'resolved'|'closed';priority:'low'|'normal'|'high'|'urgent';admin_response:string|null;responded_at:string|null;created_at:string;user?:{id:number;name:string;email:string}}
export interface Notice{id:string;data:{kind:string;title:string;message:string;target:string};read_at:string|null;created_at:string}
export interface Paginator<T>{data:T[];current_page:number;last_page:number;total:number}
