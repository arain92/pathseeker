import { Career } from '../careers/career.models';
export interface QuizOption{id:number;label:string;value:string}
export interface QuizQuestion{id:number;prompt:string;section:string;type:string;sort_order:number;options:QuizOption[]}
export interface SavedQuizAnswer{quiz_question_id:number;quiz_option_id:number}
export interface QuizAttempt{id:number;status:string;completed_at?:string;answers?:SavedQuizAnswer[];result?:QuizResult;recommendations?:Recommendation[]}
export interface QuizHistoryPage{data:QuizAttempt[];current_page:number;last_page:number;total:number}
export interface QuizResult{dimension_scores:Record<string,number>;strongest_traits:string[];recommended_domains:string[];scoring_version:string}
export interface Recommendation{id:number;rank:number;overall_score:number;factors:Record<string,number>;explanation:{strongest_alignment:string[];method:string};career:Career}
