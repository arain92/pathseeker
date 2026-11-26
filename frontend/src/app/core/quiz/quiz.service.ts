import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { ApiResponse } from '../auth/auth.models';
import { QuizAttempt, QuizHistoryPage, QuizQuestion } from './quiz.models';
@Injectable({providedIn:'root'})
export class QuizService{private readonly http=inject(HttpClient);private readonly api='/api/quiz';questions():Observable<QuizQuestion[]>{return this.http.get<ApiResponse<QuizQuestion[]>>(`${this.api}/questions`).pipe(map(r=>r.data));}start():Observable<QuizAttempt>{return this.http.post<ApiResponse<QuizAttempt>>(`${this.api}/attempts`,{}).pipe(map(r=>r.data));}answer(attempt:number,question:number,option:number):Observable<unknown>{return this.http.put(`${this.api}/attempts/${attempt}/answer`,{question_id:question,option_id:option});}complete(attempt:number):Observable<QuizAttempt>{return this.http.post<ApiResponse<QuizAttempt>>(`${this.api}/attempts/${attempt}/complete`,{}).pipe(map(r=>r.data));}result(attempt:number):Observable<QuizAttempt>{return this.http.get<ApiResponse<QuizAttempt>>(`${this.api}/attempts/${attempt}/result`).pipe(map(r=>r.data));}history():Observable<QuizHistoryPage>{return this.http.get<ApiResponse<QuizHistoryPage>>(this.api+"/history").pipe(map(r=>r.data));}}
