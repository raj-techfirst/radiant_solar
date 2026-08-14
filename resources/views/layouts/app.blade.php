<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="">
    <meta name="description" content="">
    <meta name="keywords" content="{{ env('APP_NAME') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="TechFirst">
    <title>@yield('title')</title>
    <link rel="shortcut icon" href="{{asset('img/fav.png')}}" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600" rel="stylesheet">

    <!-- start css -->
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/fontawesome.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/vendors.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/core/select2.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/tables/datatable/responsive.bootstrap5.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/tables/datatable/buttons.bootstrap5.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/tables/datatable/rowGroup.bootstrap5.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/extensions/toastr.min.css')}}">

    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/pickers/pickadate/pickadate.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css')}}">

    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/bootstrap.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('plugins/summernote/summernote-bs4.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/bootstrap-extended.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/colors.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/components.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/core/menu/menu-types/vertical-menu.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/plugins/extensions/ext-component-toastr.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/plugins/extensions/ext-component-sweet-alerts.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/extensions/sweetalert2.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/custom.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('plugins/jquery-ui/css/jquery-ui.min.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('plugins/fancybox/jquery.fancybox.css')}}">

    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/charts/chart-apex.min.css')}}">

    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/forms/wizard/bs-stepper.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/plugins/forms/form-wizard.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/style.css?a=2025')}}">

    @yield('css')

    <!-- end css -->
</head>
<!-- end head -->

<!-- start body -->

<body class="vertical-layout vertical-menu-modern navbar-floating footer-static" data-open="click" data-menu="vertical-menu-modern" data-col="">
    <div class="centered d-none">
        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
    </div>

    <!-- start header -->
    <nav class="mt-0 header-navbar navbar navbar-expand-lg align-items-center floating-nav navbar-light navbar-shadow container-xxl">
        <div class="py-0 navbar-container d-flex content">
            <div class="bookmark-wrapper d-flex align-items-center">
                <ul class="nav navbar-nav d-xl-none">
                    <li class="nav-item"><a class="nav-link menu-toggle" href="#"><i class="ficon" data-feather="menu"></i></a></li>
                </ul>
            </div>
            @if(session()->get('soft') == 'erp')
            <ul class="nav navbar-nav align-items-center">
                <li class="nav-item text-danger h4 mb-0"><span>{!! getSelectedYearIsCurrent() !!}</span></li>
            </ul>
            @endif

            <ul class="nav navbar-nav align-items-center ms-auto">

                <li class="nav-item dropdown" id="header_notification_bar"></li>
                <li class="nav-item dropdown"></li>
                @can('language')
                <li class="nav-item dropdown dropdown-user mx-1">
                    <div class="btn-group">
                        @if(session()->get('locale') == 'en')
                        <button type="button" class="btn btn-sm btn-outline-primary">English</button>
                        @else
                        <button type="button" class="btn btn-sm btn-outline-primary">English</button>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu w-75 m-0 dropdown-menu-start">
                            <a class="dropdown-item lang-change {{ session()->get('locale') == 'en' ? 'active' : '' }}" href="javascript:void(0);" data-value="en">English</a>
                            <a class="dropdown-item lang-change {{ session()->get('locale') == 'hn' ? 'active' : '' }}" href="javascript:void(0);" data-value="hn">à¤¹à¤¿à¤‚à¤¦à¥€</a>
                            <a class="dropdown-item lang-change {{ session()->get('locale') == 'gj' ? 'active' : '' }}" href="javascript:void(0);" data-value="gj">àª—à«àªœàª°àª¾àª¤à«€</a>
                        </div>
                    </div>
                </li>
                @endcan
                <!-- @if(Auth::user()->if_erp == 1) -->
                <li class="nav-item dropdown dropdown-user mx-1">
                    <div class="btn-group">
                        @if(session()->get('soft') == 'erp')
                        <button type="button" class="btn btn-sm btn-outline-primary">ERP</button>
                        @else
                        <button type="button" class="btn btn-sm btn-outline-primary">CRM</button>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu w-75 m-0 dropdown-menu-start">
                            <a class="dropdown-item soft-change {{ session()->get('soft') == 'crm' ? 'active' : '' }}" href="javascript:void(0);" data-value="crm" id="first_crm">CRM</a>
                            <a class="dropdown-item soft-change {{ session()->get('soft') == 'erp' ? 'active' : '' }}" href="javascript:void(0);" data-value="erp">ERP</a>
                        </div>
                    </div>
                </li>
                @if(session()->get('soft') == 'erp')
                <li class="nav-item dropdown dropdown-user mx-1">
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary">{{ session()->get('year') }}</button>
                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu w-75 m-0 dropdown-menu-start">
                            {!! getYear() !!}
                        </div>
                    </div>
                </li>
                @endif
                <!-- @endif -->
                <li class="nav-item dropdown dropdown-user">
                    <a class="nav-link dropdown-toggle dropdown-user-link" id="dropdown-user" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <div class="user-nav d-sm-flex d-none">
                            <span class="user-name fw-bolder">{{ ucfirst(Auth::user()->name) }}</span>
                            <span class="user-status">{{ ucfirst(Auth::user()->roles[0]->name) }}</span>
                        </div>
                        <span class="avatar">
                            <img class="round" src="{{asset('img/user.png')}}" alt="avatar" height="40" width="40">
                            <span class="avatar-status-online"></span>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdown-user">
                        <!-- <a class="dropdown-item @if(Request::segment(1) == 'profile'): active @endif" href="{{ route('profile.create') }}"><i class="me-50" data-feather="user"></i> {{ __('message.Profile') }}</a> -->
                        <a class="dropdown-item @if(Request::segment(1) == 'change-password'): active @endif" href="{{ route('change-password') }}"><i class="me-50" data-feather="lock"></i> {{ __('message.Change Password') }}</a>
                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                               document.getElementById('logout-form').submit();"><i class="me-50" data-feather="power"></i> {{ __('message.Logout') }}</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                        <!-- <a class="dropdown-item @if(Request::segment(1) == 'view-message'): active @endif" href="{{ route('view-message') }}"><i class="me-50" data-feather='message-circle'></i> Message</a> -->
                    </div>
                </li>
            </ul>
        </div>
    </nav>
    <!-- end header-->

    <!-- start main menu -->
    <div class="main-menu menu-fixed menu-light menu-accordion menu-shadow expanded" data-scroll-to-active="true">
        <div class="navbar-header">
            <ul class="nav navbar-nav flex-row">
                <li class="nav-item me-auto">
                    <a class="navbar-brand" href="{{route('home')}}">
                        <span class="brand-logo">
                            <img src="{{asset('img/favicon.png')}}">
                        </span>
                        <img src="{{asset('img/logo.png')}}" class="logo-admin">
                    </a>
                </li>
                <li class="nav-item nav-toggle">
                    <a class="nav-link modern-nav-toggle pe-0" data-bs-toggle="collapse">
                        <i class="d-block d-xl-none text-primary toggle-icon font-medium-4" data-feather="x"></i>
                        <i class="d-none d-xl-block collapse-toggle-icon font-medium-4  text-primary" data-feather="disc" data-ticon="disc"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="shadow-bottom"></div>
        <div class="main-menu-content">
            <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
                <li class="nav-item @if(Request::segment(1) == 'home') active @endif">
                    <a class="d-flex align-items-center" href="{{route('home')}}">
                        <i data-feather="home"></i>
                        <span class="menu-title text-truncate" data-i18n="Dashboards">{{ __('message.Dashboard') }}</span>
                    </a>
                </li>

                @can('permission-list')
                <li class="nav-item @if(Request::segment(1) == 'permission'): active @endif">
                    <a class="d-flex align-items-center" href="{{route('permission.index')}}">
                        <i data-feather='package'></i>
                        <span class="menu-title text-truncate">{{ __('message.Permission') }}</span>
                    </a>
                </li>
                @endcan
                @can('role-list')
                <li class="nav-item @if(Request::segment(1) == 'role'): active @endif">
                    <a class="d-flex align-items-center" href="{{route('role.index')}}">
                        <i data-feather='dribbble'></i>
                        <span class="menu-title text-truncate">{{ __('message.Role') }}</span>
                    </a>
                </li>
                @endcan
                @can('user-list')
                <li class="nav-item @if(Request::segment(1) == 'user') active @endif">
                    <a class="d-flex align-items-center" href="{{route('user-list')}}">
                        <i data-feather='user'></i>
                        <span class="menu-title text-truncate">{{ __('message.User') }}</span>
                    </a>
                </li>
                @endcan


                @canany(['category-list', 'product-list','district-list','taluka-list','village-list','source-list','unit-list','bank-list','policy-list','sub-division-list'])
                <li class="nav-item has-sub @if(Request::segment(2) == 'category' || Request::segment(2) == 'product' || Request::segment(2) == 'district' || Request::segment(2) == 'sub-division-list' ||  Request::segment(2) == 'taluka' || Request::segment(2) == 'village' || Request::segment(2) == 'source' || Request::segment(2) == 'unit' || Request::segment(2) == 'bank' || Request::segment(2) == 'policy') sidebar-group-active open @endif">
                    <a class="d-flex align-items-center" href="#">
                        <i data-feather="grid"></i>
                        <span data-i18n="Forms &amp; Tables">{{ __('message.Master') }}</span>
                    </a>
                    <ul class="menu-content">
                        @if(session()->get('soft') == 'erp')
                        @can('category-list')
                        <li class="nav-item @if(Request::segment(1) == 'category' ) active @endif">
                            <a href="{{ route('category.index') }}" class="d-flex align-items-center">
                                <i data-feather='maximize'></i>
                                <span class="menu-title text-truncate">Category</span>
                            </a>
                        </li>
                        @endcan
                        @can('unit-list')
                        <li class="nav-item @if(Request::segment(1) == 'unit' ) active @endif">
                            <a href="{{ route('unit.index') }}" class="d-flex align-items-center">
                                <i data-feather='maximize'></i>
                                <span class="menu-title text-truncate">Unit</span>
                            </a>
                        </li>
                        @endcan
                        @can('product-list')
                        <li class="nav-item @if(Request::segment(1) == 'product' ) active @endif">
                            <a href="{{ route('product.index') }}" class="d-flex align-items-center">
                                <i data-feather='codesandbox'></i>
                                <span class="menu-title text-truncate">Bill of Supply</span>
                            </a>
                        </li>
                        @endcan
                        @can('item-group-list')
                        <li class="nav-item @if(Request::segment(1) == 'item-group' ) active @endif">
                            <a href="{{ route('item-group.index') }}" class="d-flex align-items-center">
                                <i data-feather='codesandbox'></i>
                                <span class="menu-title text-truncate">Panel/Inverter</span>
                            </a>
                        </li>
                        @endcan
                        @can('year-list')
                        <li class="nav-item @if(Request::segment(1) == 'year' ) active @endif">
                            <a href="{{ route('year.index') }}" class="d-flex align-items-center">
                                <i data-feather='codesandbox'></i>
                                <span class="menu-title text-truncate">Year</span>
                            </a>
                        </li>
                        @endcan
                        @can('supplier-list')
                        <li class="nav-item @if(Request::segment(1) == 'supplier' ) active @endif">
                            <a href="{{ route('supplier.index') }}" class="d-flex align-items-center">
                                <i data-feather='codesandbox'></i>
                                <span class="menu-title text-truncate">Supplier</span>
                            </a>
                        </li>
                        @endcan
                        @can('bom-list')
                        <li class="nav-item @if(Request::segment(1) == 'bom' ) active @endif">
                            <a href="{{ route('bom.index') }}" class="d-flex align-items-center">
                                <i data-feather='list'></i>
                                <span class="menu-title text-truncate">BOM</span>
                            </a>
                        </li>
                        @endcan
                        @endif
                        @if(session()->get('soft') == 'crm')
                        @can('category-list')
                        <li class="nav-item @if(Request::segment(1) == 'category' ) active @endif">
                            <a href="{{ route('category.index') }}" class="d-flex align-items-center">
                                <i data-feather='box'></i>
                                <span class="menu-title text-truncate" data-i18n="Category">{{ __('message.Category') }}</span>
                            </a>
                        </li>
                        @endcan

                        @can('district-list')
                        <li class="nav-item @if(Request::segment(1) == 'district' ) active @endif">
                            <a href="{{ route('district.index') }}" class="d-flex align-items-center">
                                <i data-feather='map-pin'></i>
                                <span class="menu-title text-truncate" data-i18n="state">District</span>
                            </a>
                        </li>
                        @endcan
                        @can('taluka-list')
                        <li class="nav-item @if(Request::segment(1) == 'taluka' ) active @endif">
                            <a href="{{ route('taluka.index') }}" class="d-flex align-items-center">
                                <i data-feather='map'></i>
                                <span class="menu-title text-truncate" data-i18n="taluka">Taluka</span>
                            </a>
                        </li>
                        @endcan

                       @can('lead-source-list')
                        <li class="nav-item @if(Request::segment(1) == 'lead-source' ) active @endif">
                            <a href="{{ route('lead-source.index') }}" class="d-flex align-items-center">
                                <i data-feather='instagram'></i>
                                <span class="menu-title text-truncate">Lead Source</span>
                            </a>
                        </li>
                        @endcan

                        @can('bank-list')
                        <li class="nav-item @if(Request::segment(1) == 'bank'): active @endif">
                            <a class="d-flex align-items-center" href="{{route('bank.index')}}">
                                <i data-feather='dribbble'></i>
                                <span class="menu-title text-truncate">Bank Master</span>
                            </a>
                        </li>
                        @endcan
                        @can('policy-list')
                        <li class="nav-item @if(Request::segment(1) == 'policy'): active @endif">
                            <a class="d-flex align-items-center" href="{{route('policy.create')}}">
                                <i data-feather='dribbble'></i>
                                <span class="menu-title text-truncate">Terms & Conditions</span>
                            </a>
                        </li>
                        @endcan
                        @can('sub-division-list')
                        <li class="nav-item @if(Request::segment(1) == 'sub-division'): active @endif">
                            <a class="d-flex align-items-center" href="{{route('sub-division.index')}}">
                                <i data-feather='dribbble'></i>
                                <span class="menu-title text-truncate">Sub Division</span>
                            </a>
                        </li>
                        @endcan
                        @can('discom-list')
                        <li class="nav-item @if(Request::segment(1) == 'discom'): active @endif">
                            <a class="d-flex align-items-center" href="{{route('discom.index')}}">
                                <i data-feather='dribbble'></i>
                                <span class="menu-title text-truncate">DISCOM</span>
                            </a>
                        </li>
                        @endcan
                        @endif
                    </ul>
                </li>
                @endcanany

                @if(session()->get('soft') == 'erp')
                @canany(['warehouse-list','warehouse-stock-list','warehouse-stock-adjust-list'])
                <li class="nav-item has-sub @if(Request::segment(2) == 'warehouse') sidebar-group-active open @endif">
                    <a class="d-flex align-items-center" href="#">
                        <i data-feather="bookmark"></i>
                        <span>Warehouse Manage</span>
                    </a>
                    <ul class="menu-content">
                        @can('warehouse-list')
                        <li class="nav-item @if(Request::segment(1) == 'warehouse' ) active @endif">
                            <a href="{{ route('warehouse.index') }}" class="d-flex align-items-center">
                                <i data-feather='codesandbox'></i>
                                <span class="menu-title text-truncate">Warehouse</span>
                            </a>
                        </li>
                        @endcan

                        @can('warehouse-stock-list')
                        <li class="nav-item @if(Request::segment(1) == 'warehouse-stock' ) active @endif">
                            <a href="{{ route('warehouse-stock.index') }}" class="d-flex align-items-center">
                                <i data-feather='codesandbox'></i>
                                <span class="menu-title text-truncate">Warehouse Stock</span>
                            </a>
                        </li>
                        @endcan

                        @can('warehouse-stock-adjust-list')
                        <li class="nav-item @if(Request::segment(1) == 'stock-adjustments') active @endif">
                            <a href="{{ route('stock-adjustments.index') }}" class="d-flex align-items-center">
                                <i data-feather='clipboard'></i>
                                <span class="menu-title text-truncate">Stock Adjustments</span>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                @can('purchase-order-list')
                <li class="nav-item @if(Request::segment(1) == 'purchase-order' ) active @endif">
                    <a href="{{ route('purchase-order.index') }}" class="d-flex align-items-center">
                        <i data-feather='shopping-cart'></i>
                        <span class="menu-title text-truncate">Purchase Order</span>
                    </a>
                </li>
                @endcan

                @can('purchase-direct-list')
                <li class="nav-item @if(Request::segment(1) == 'purchase-direct' ) active @endif">
                    <a href="{{ route('purchase-direct.index') }}" class="d-flex align-items-center">
                        <i data-feather='codesandbox'></i>
                        <span class="menu-title text-truncate">Goods Receipt</span>
                    </a>
                </li>
                @endcan

                @can('delivery-challan-list')
                <li class="nav-item @if(Request::segment(1) == 'delivery-challan') active @endif">
                    <a href="{{ route('delivery-challan.index') }}" class="d-flex align-items-center">
                        <i data-feather='truck'></i>
                        <span class="menu-title text-truncate">Goods Issue</span>
                    </a>
                </li>
                @endcan

                @can('delivery-challan-return-list')
                <li class="nav-item @if(Request::segment(1) == 'delivery-challan-return') active @endif">
                    <a href="{{ route('delivery-challan-return.index') }}" class="d-flex align-items-center">
                        <i data-feather='truck' class="flip-horizontal"></i>
                        <span class="menu-title text-truncate">Goods Return</span>
                    </a>
                </li>
                @endcan


                @can('project-wise-stock-list')
                <li class="nav-item @if(Request::segment(1) == 'project-wise-stock') active @endif">
                    <a href="{{ route('project-wise-stock.index') }}" class="d-flex align-items-center">
                        <i data-feather='codesandbox'></i>
                        <span class="menu-title text-truncate">Project Wise Stock</span>
                    </a>
                </li>
                @endcan

                @can('project-stock-adjust-list')
                <li class="nav-item @if(Request::segment(1) == 'project-stock-adjustments' ) active @endif">
                    <a href="{{ route('project-stock-adjustments.index') }}" class="d-flex align-items-center">
                        <i data-feather='clipboard'></i>
                        <span class="menu-title text-truncate">Project Adjust Stock</span>
                    </a>
                </li>
                @endcan

                @canany(['get-serial-numbers','project-wise-stock-report','project-wise-dispach'])
                <li class="nav-item @if(Request::segment(1) == 'reports'): active @endif">
                    <a class="d-flex align-items-center" href="{{route('erp-reports')}}">
                        <i data-feather='list'></i>
                        <span class="menu-title text-truncate">Reports</span>
                    </a>
                </li>
                @endcanany

                @endif

                @if(session()->get('soft') == 'crm')
                @canany(['penal-company-list', 'penal-type-list', 'penal-watt-list', 'inveter-company-list'])
                <li class="nav-item has-sub @if(Request::segment(2) == 'penal-company' || Request::segment(2) == 'penal-type' || Request::segment(2) == 'penal-watt' || Request::segment(2) == 'inveter-company')') sidebar-group-active open @endif">
                    <a class="d-flex align-items-center" href="#">
                        <i data-feather="grid"></i>
                        <span data-i18n="Forms &amp; Tables">Panel Master</span>
                    </a>
                    <ul class="menu-content">
                        @can('penal-company-list')
                        <li class="nav-item @if(Request::segment(1) == 'penal-company'): active @endif">
                            <a class="d-flex align-items-center" href="{{route('penal-company.index')}}">
                                <i data-feather='alert-circle'></i>
                                <span class="menu-title text-truncate">Panel Company</span>
                            </a>
                        </li>
                        @endcan
                        @can('penal-type-list')
                        <li class="nav-item @if(Request::segment(1) == 'penal-type'): active @endif">
                            <a class="d-flex align-items-center" href="{{route('penal-type.index')}}">
                                <i data-feather='alert-octagon'></i>
                                <span class="menu-title text-truncate">Panel Type</span>
                            </a>
                        </li>
                        @endcan
                        @can('penal-watt-list')
                        <li class="nav-item @if(Request::segment(1) == 'penal-watt'): active @endif">
                            <a class="d-flex align-items-center" href="{{route('penal-watt.index')}}">
                                <i data-feather='alert-triangle'></i>
                                <span class="menu-title text-truncate">Panel Watts</span>
                            </a>
                        </li>
                        @endcan
                        @can('inveter-company-list')
                        <li class="nav-item @if(Request::segment(1) == 'inveter-company'): active @endif">
                            <a class="d-flex align-items-center" href="{{route('inveter-company.index')}}">
                                <i data-feather='file-plus'></i>
                                <span class="menu-title text-truncate">Inverter Company</span>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany
                @can('employee-list')
                <li class="nav-item @if(Request::segment(1) == 'employee') active @endif">
                    <a class="d-flex align-items-center" href="{{route('employee.index')}}">
                        <i data-feather="users"></i>
                        <span class="menu-title text-truncate" data-i18n="Employee">Users</span>
                    </a>
                </li>
                @endcan
                @can('sales-list')
                <li class="nav-item @if(Request::segment(1) == 'sales') active @endif">
                    <a class="d-flex align-items-center" href="{{route('sales.index')}}">
                        <i data-feather="users"></i>
                        <span class="menu-title text-truncate" data-i18n="sales">{{ __('message.Sales Officer') }}</span>
                    </a>
                </li>
                @endcan
                @can('lead-list')
                <li class="nav-item @if(Request::segment(1) == 'lead') active @endif">
                    <a class="d-flex align-items-center" href="{{route('lead.index')}}">
                        <i data-feather="phone-incoming"></i>
                        <span class="menu-title text-truncate" data-i18n="lead">{{ __('message.Lead') }}</span>
                    </a>
                </li>
                <li class="nav-item @if(Request::segment(1) == 'lead-complet') active @endif">
                    <a class="d-flex align-items-center" href="{{route('lead-complet')}}">
                        <i data-feather="phone-incoming"></i>
                        <span class="menu-title text-truncate" data-i18n="lead">Completed Lead</span>
                    </a>
                </li>
                @endcan
                @can('sales-quatation-list')
                <li class="nav-item @if(Request::segment(1) == 'sales-quatation') active @endif">
                    <a class="d-flex align-items-center" href="{{route('sales-quatation.index')}}">
                        <i data-feather="pie-chart"></i>
                        <span class="menu-title text-truncate" data-i18n="sales-quatation">{{ __('message.Sales Quatation') }}</span>
                    </a>
                </li>
                @endcan
                @can('estimate-list')
                <li class="nav-item @if(Request::segment(1) == 'estimate') active @endif">
                    <a class="d-flex align-items-center" href="{{route('estimate.index')}}">
                        <i data-feather="slack"></i>
                        <span class="menu-title text-truncate" data-i18n="lead">{{ __('message.Estimate') }}</span>
                    </a>
                </li>
                @endcan
                @can('task-list')
                <li class="nav-item @if(Request::segment(1) == 'task') active @endif">
                    <a class="d-flex align-items-center" href="{{route('task.index')}}">
                        <i data-feather="film"></i>
                        <span class="menu-title text-truncate" data-i18n="task">{{ __('message.Task') }}</span>
                    </a>
                </li>
                @endcan
                @can('sales-master-list')
                <li class="nav-item @if(Request::segment(1) == 'sales-master'): active @endif">
                    <a class="d-flex align-items-center" href="{{route('sales-master.index')}}">
                        <i data-feather='dribbble'></i>
                        <span class="menu-title text-truncate">Sales Order</span>
                    </a>
                </li>
                @endcan
                @can('payment-collection-list')
                <li class="nav-item @if(Request::segment(1) == 'payment-collection'): active @endif">
                    <a class="d-flex align-items-center" href="{{route('payment-collection.index')}}">
                        <i data-feather='credit-card'></i>
                        <span class="menu-title text-truncate">Payment Collection</span>
                    </a>
                </li>
                @endcan
                @can('commission-payment-list')
                <li class="nav-item @if(Request::segment(1) == 'commission-payment'): active @endif">
                    <a class="d-flex align-items-center" href="{{route('commission-payment.index')}}">
                        <i data-feather='credit-card'></i>
                        <span class="menu-title text-truncate">Commission Payment</span>
                    </a>
                </li>
                @endcan
                @can('commission-list')
                <li class="nav-item @if(Request::segment(1) == 'commission-list'): active @endif">
                    <a class="d-flex align-items-center" href="{{route('commission-list.index')}}">
                        <i data-feather='list'></i>
                        <span class="menu-title text-truncate">Commission List</span>
                    </a>
                </li>
                @endcan
                @canany(['reports-total-collection','reports-payment-pending','reports-meter-charges','reports-dispach','reports-installation','reports-meter-application','reports-final','reports-invoice','b2b-accept','b2b-dispatch','b2b-rate','sales-agent-wise-report'])
                <li class="nav-item @if(Request::segment(1) == 'reports' || Request::segment(1) == 'total-collection-reports' || Request::segment(1) == 'payment-pending-reports'  || Request::segment(1) == 'invoice-reports'  || Request::segment(1) == 'meter-charges-reports' || Request::segment(1) == 'dispach-reports' || Request::segment(1) == 'installation-reports' || Request::segment(1) == 'meter-application-reports' || Request::segment(1) == 'final-reports'): active @endif">
                    <a class="d-flex align-items-center" href="{{route('reports')}}">
                        <i data-feather='list'></i>
                        <span class="menu-title text-truncate">Reports</span>
                    </a>
                </li>
                @endcanany
                @can('rate-calculator-list')
                <li class="nav-item @if(Request::segment(1) == 'rate-calculator'): active @endif">
                    <a class="d-flex align-items-center" href="{{route('rate-calculator.index')}}">
                        <i class='fa fa-calculator'></i>
                        <span class="menu-title text-truncate">Rate Calculator</span>
                    </a>
                </li>
                @endcan
                @can('inquiry-list')
                <li class="nav-item @if(Request::segment(1) == 'inquiry-list') : active @endif">
                    <a class="d-flex align-items-center" href="{{route('inquiry-list')}}">
                        <i data-feather='message-circle'></i>
                        <span class="menu-title text-truncate">Complaint Management</span>
                    </a>
                </li>
                @endcan
                @endif

            </ul>
        </div>
    </div>
    <!-- end main menu -->

    <!-- start content -->
    <div class="app-content content">
        <div class="content-overlay">
        </div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
            </div>
            <div class="content-body">
                @yield('content')
            </div>
        </div>
    </div>
    <!-- end content -->


    <!-- start change password model -->
    <div class="modal fade" id="changeModal" tabindex="-1" aria-labelledby="exampleModalLabel1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-transparent border-bottom">
                    <h4 class="card-title mb-1">{{ __('message.Change') }} <small>{{ __('message.Password') }}</small></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-1">
                    <form id="password_form" action="javascript:void(0)" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-12 mb-1 custom-input-group form-password-toggle">
                                <label class="form-label" for="current_password">{{ __('message.Current Password') }} <span class="text-danger">*</span></label>
                                <div class="input-group ">
                                    <input type="password" class="form-control" name="current_password" id="current_password" placeholder="{{ __('message.Current Password') }}" aria-describedby="basic-default-password2">
                                    <span id="basic-default-password2" class="input-group-text cursor-pointer toggle-password">
                                        <i class="fa fa-eye"></i>
                                    </span>
                                </div>
                                <span class="invalid-feedback d-block" id="error_current_password" role="alert"></span>
                            </div>
                            <div class="col-12 mb-1 custom-input-group form-password-toggle">
                                <label class="form-label" for="password">{{ __('message.New Password') }} <span class="text-danger">*</span></label>
                                <div class="input-group ">
                                    <input type="password" class="form-control" name="password" id="password" placeholder="{{ __('message.New Password') }}" aria-describedby="basic-default-password3">
                                    <span id="basic-default-password3" class="input-group-text cursor-pointer toggle-password">
                                        <i class="fa fa-eye"></i>
                                    </span>
                                </div>
                                <span class="invalid-feedback d-block" id="error_password" role="alert"></span>
                            </div>
                            <div class="col-12 mb-1 custom-input-group form-password-toggle">
                                <label class="form-label" for="confirm_password">{{ __('message.Confirm Password') }} <span class="text-danger">*</span></label>
                                <div class="input-group ">
                                    <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="{{ __('message.Confirm Password') }}" aria-describedby="basic-default-password4">
                                    <span id="basic-default-password4" class="input-group-text cursor-pointer toggle-password">
                                        <i class="fa fa-eye"></i>
                                    </span>
                                </div>
                                <span class="invalid-feedback d-block" id="error_confirm_password" role="alert"></span>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-sm btn-primary float-end change-password">{{ __('message.Submit') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- end change password model -->

    <!-- start footer -->
    <footer class="footer footer-static footer-light">
        <p class="clearfix mb-0">
            <span class="float-md-start d-block d-md-inline-block mt-25">COPYRIGHT &copy; 2024<a class="ms-25" href="#" target="_blank">{{ env("APP_NAME") }}</a>
                <span class="d-none d-sm-inline-block"> All Rights Reserved.</span>
            </span>
        </p>
    </footer>
    <button class="btn btn-primary btn-icon scroll-top" type="button"><i data-feather="arrow-up"></i></button>
    <!-- end footer -->

    <!-- start script -->
    <script src="{{asset('app-assets/vendors/js/vendors.min.js')}}"></script>
    <script src="{{asset('plugins/jquery-ui/js/jquery.validate.min.js')}}"></script>
    <script src="{{asset('plugins/jquery-ui/js/jquery-ui.min.js')}}"></script>
    <script src="{{asset('plugins/jquery-ui/js/ckeditor.js')}}"></script>
    <script src="{{asset('app-assets/js/form/select2.full.min.js')}}"></script>
    <script src="{{asset('plugins/fancybox/jquery.fancybox.min.js')}}"></script>
    <script src="{{asset('app-assets/js/form/form-select2.js')}}"></script>

    <script src="{{asset('app-assets/vendors/js/pickers/pickadate/picker.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/pickers/pickadate/picker.date.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/pickers/pickadate/picker.time.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/pickers/pickadate/legacy.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js')}}"></script>

    <!-- <script src="{{asset('app-assets/vendors/js/jquery.repeater.min.js')}}"></script> -->
    <script src="{{asset('app-assets/vendors/js/extensions/sweetalert2.all.min.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/tables/datatable/dataTables.bootstrap5.min.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/tables/datatable/responsive.bootstrap5.min.js')}}"></script>
    <script src="{{asset('app-assets/js/scripts/extensions/ext-component-sweet-alerts.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/extensions/toastr.min.js')}}"></script>
    <script src="{{asset('app-assets/js/scripts/extensions/ext-component-toastr.js')}}"></script>
    <script src="{{asset('plugins/summernote/summernote-bs4.min.js')}}"></script>

    <script src="{{asset('app-assets/vendors/js/forms/repeater/jquery.repeater.min.js')}}"></script>
    <script src="{{asset('app-assets/js/scripts/forms/form-repeater.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/charts/apexcharts.min.js')}}"></script>

    <script src="{{asset('app-assets/js/core/app-menu.js')}}"></script>
    <script src="{{asset('app-assets/js/core/app.js')}}"></script>
    <script src="{{asset('js/app.js')}}"></script>
    <script src="{{asset('app-assets/js/form/bs-stepper.js')}}"></script>
    <script src="{{asset('app-assets/js/form/form-wizard-icons.js')}}"></script>
    @yield('script')
    <script>
        $('.table').on('show.bs.dropdown', '.btn-group', function() {
            let dropdownMenu = $(this).find('.dropdown-menu');
            dropdownMenu.appendTo('body');
        });

        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
            $('.flatpickr-date-time').flatpickr({
                enableTime: true
            });
        });

        $('.summernote').summernote({
            height: 200,
            minHeight: 200,
            maxHeight: 600,
        });

        //change language
        var url = "{{route('language')}}";
        $(".lang-change").click(function() {
            window.location.href = url + "?lang=" + $(this).data('value');
        });
        var url = "{{route('soft')}}";
        $(".soft-change").click(function() {
            window.location.href = url + "?soft=" + $(this).data('value');
        });
        $(".year-change").click(function() {
            window.location.href = "{{route('years')}}" + "?year=" + $(this).data('value');
        });
        $("#changeModal").on("hidden.bs.modal", function(e) {
            $(this).find('form_password').trigger('reset');
            $(".custom-error").html("");
            $(".invalid-feedback").html("");
        });
        $('.toggle-password').click(function() {
            $(this).children().toggleClass('fa fa-eye fa fa-eye-slash');
        });
        $(document).on('click', '.change-password', function() {
            var formData = new FormData($("#password_form")[0]);
            if ($("#current_password").val() != "" && $("#password").val() != "" && $("#confirm_password").val() != "") {
                $.ajax({
                    type: "POST",
                    url: "{{route('update-password')}}",
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $("#error_confirm_password").html(' ');
                        $(".change-password").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('message.Wait') }}`);
                        $(".change-password").attr('disabled', true);
                    },
                    success: function(response) {
                        $(".change-password").html("{{ __('message.Submit') }}");
                        $(".change-password").attr('disabled', false);
                        if (response.server_error && response.status == false) {
                            toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                        } else if (response.status == false && response.label) {
                            toastr.warning("{{ __('message.Current password does not match.') }}", "{{ __('message.Warning') }}");
                        } else if (response.status == false) {
                            $.each(response.errors, function(key, value) {
                                $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                            });
                            toastr.warning("{{ __('message.Please input proper data.') }}", "{{ __('message.Warning') }}");
                        } else {
                            $('#form')[0].reset();
                            toastr.success("{{ __('message.Password updated successfully.') }}", "{{ __('message.Success') }}");
                            location.reload(true);
                        }
                    }
                });
            } else {
                $("#password_form").validate({
                    rules: {
                        current_password: {
                            required: true,
                        },
                        password: {
                            required: true,
                        },
                        confirm_password: {
                            required: true,
                        },
                    },
                    messages: {
                        current_password: {
                            required: "{{ __('message.Enter current password') }}",
                        },
                        password: {
                            required: "{{ __('message.Enter new password') }}",
                        },
                        confirm_password: {
                            required: "{{ __('message.Enter confirm password') }}",
                        },
                    },
                    errorElement: "p",
                    errorClass: "text-danger mb-0 custom-error",

                    highlight: function(element) {
                        $(element).addClass('has-error');
                    },
                    unhighlight: function(element) {
                        $(element).removeClass('has-error');
                    },
                    errorPlacement: function(error, element) {
                        $(element).closest('.custom-input-group').append(error);
                    }
                });
            }
        });
        $(document).ready(function() {
            var soft = "{{session()->get('soft')}}";
            if (soft == "") {
                window.location.href = url + "?soft=crm";
                // $("#first_crm").trigger('click');
            }
        });

        document.querySelectorAll('.menu-item.has-submenu > a').forEach(function(menuItem) {
            menuItem.addEventListener('click', function() {
                let parentItem = menuItem.parentElement;
                parentItem.classList.toggle('open');
            });
        });
    </script>
    @yield('pagescript')
    <!-- end script -->
</body>
<!-- end body -->

</html>
