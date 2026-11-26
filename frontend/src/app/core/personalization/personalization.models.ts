import { Career } from '../careers/career.models';
import { LibraryResource,MediaItem,Story } from '../content/content.models';

export type SavedContent=Career|LibraryResource|MediaItem|Story;
export interface SavedItem { id:number; type:'career'|'resource'|'multimedia'|'story'; note:string|null; updated_at:string; item:SavedContent }
export interface RecentItem { id:number; type:'career'|'resource'|'multimedia'; first_viewed_at:string; last_viewed_at:string; view_count:number; item:Career|LibraryResource|MediaItem }
export interface DashboardData {
  passport:{completion:number;skills_count:number;interests_count:number};
  latest_quiz:null|{attempt_id:number;completed_at:string;dimensions:Record<string,number>;recommendations:{score:number;factors:Record<string,number>;career:Career}[]};
  saved:{bookmark_id:number;note:string|null;career:Career}[];
  recent:{last_viewed_at:string;view_count:number;career:Career}[];
  similar:{career:Career}[];
  trending:{engagement_count:number;career:Career}[];
}
