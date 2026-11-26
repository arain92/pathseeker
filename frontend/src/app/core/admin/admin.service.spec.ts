import { TestBed } from "@angular/core/testing";
import { provideHttpClient } from "@angular/common/http";
import { HttpTestingController, provideHttpClientTesting } from "@angular/common/http/testing";
import { AdminService } from "./admin.service";

describe("AdminService", () => {
  let service: AdminService;
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({providers:[AdminService,provideHttpClient(),provideHttpClientTesting()]});
    service=TestBed.inject(AdminService);
    http=TestBed.inject(HttpTestingController);
  });

  afterEach(() => http.verify());

  it("requests the selected resource by ID", () => {
    service.resource(42).subscribe();
    http.expectOne("/api/admin/resources/42").flush({success:true,message:"ok",data:{}});
  });

  it("requests the selected multimedia item by ID", () => {
    service.mediaItem(17).subscribe();
    http.expectOne("/api/admin/multimedia/17").flush({success:true,message:"ok",data:{}});
  });
});
