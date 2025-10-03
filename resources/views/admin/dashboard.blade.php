@extends('admin.layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">					
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Default box -->
        <div class="container-fluid">
            <div class="row">
                
                <div class="col-lg-3 col-6">							
                    <div class="small-box card">
                        <div class="inner">
                            <h3><i class="nav-icon  fas fa-users"></i></h3>
                            {{-- <h5>Total Users: <strong>{{ $total_users }}</strong> </h5> --}}
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                        {{-- <a href="{{ route('admin.users.index') }}" class="small-box-footer text-dark">More info <i class="fas fa-arrow-circle-right"></i></a> --}}
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">							
                    <div class="small-box card">
                        <div class="inner">
                            <h3><i class='fas fa-user-secret' style='font-size:24px;color:rgb(25, 242, 10)'></i></h3>
                            {{-- <h5>Total DG's: <strong>{{ $total_DGs }}</strong> </h5> --}}
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                        {{-- <a href="{{ route('admin.users.index') }}" class="small-box-footer text-dark">More info <i class="fas fa-arrow-circle-right"></i></a> --}}
                    </div>
                </div>

                <div class="col-lg-3 col-6">							
                    <div class="small-box card">
                        <div class="inner">
                            <h3><i class='fas fa-user-tie' style='font-size:24px;color:rgb(36, 110, 229)'></i></h3>
                            {{-- <h5>Total Dr's: <strong>{{ $total_Drs }}</strong> </h5> --}}
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                        {{-- <a href="{{ route('admin.users.index') }}" class="small-box-footer text-dark">More info <i class="fas fa-arrow-circle-right"></i></a> --}}
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">							
                    <div class="small-box card">
                        <div class="inner">
                            <h3><i class='fas fa-user-cog' style='font-size:24px;color:rgb(0, 183, 255)'></i></i></h3>
                            {{-- <h5>Total Employees: <strong>{{ $total_Employees }}</strong> </h5> --}}
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                        {{-- <a href="{{ route('admin.users.index') }}" class="small-box-footer text-dark">More info <i class="fas fa-arrow-circle-right"></i></a> --}}
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">							
                    <div class="small-box card">
                        <div class="inner">
                            <h3><i class='fas fa-user-times' style='font-size:24px;color:rgb(255, 0, 0)'></i></i></h3>
                            {{-- <h5>Total Pending: <strong style="color: red">{{ $total_Pending }}</strong> </h5> --}}
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                        {{-- <a href="{{ route('admin.users.index') }}" class="small-box-footer text-dark">More info <i class="fas fa-arrow-circle-right"></i></a> --}}
                    </div>
                </div>
                
            </div>
        </div>					
        <!-- /.card -->
    </section>
    <!-- /.content -->
@endsection