@extends('layouts.mainApp')

@section('content')

<div class="container emp-profile">
    <form method="POST" action="{{route('auth.businessmen.update',['id' => $user->id])}}">
        {{ csrf_field() }}
        {{ method_field('PATCH') }}
        <div class="row">
            <div class="col-md-4">
                <div class="profile-img">
                    <img src="{{ $userInfo->photo }}" alt="avatar"/>
                    <!-- <div class="file btn btn-lg btn-primary">
                        Change Photo
                        <input type="file" name="file"/>
                    </div> -->
                </div>
            </div>
            <div class="col-md-6">
                <div class="profile-head">
                    <h5>
                        {{ $user->name }}
                    </h5>
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">About</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-2">
                <input type="submit" class="profile-edit-btn" value="Сохранить"/>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="profile-work">
                    <!-- <p>WORK LINK</p>
                    <a href="">Website Link</a><br/>
                    <a href="">Bootsnipp Profile</a><br/>
                    <a href="">Bootply Profile</a>
                    <p>SKILLS</p>
                    <a href="">Web Designer</a><br/>
                    <a href="">Web Developer</a><br/>
                    <a href="">WordPress</a><br/>
                    <a href="">WooCommerce</a><br/>
                    <a href="">PHP, .Net</a><br/> -->
                </div>
            </div>
            <div class="col-md-8">
                <div class="tab-content profile-tab" id="myTabContent">
                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                        <div class="row py-1">
                            <div class="col-md-6">
                                <label>Логин</label>
                            </div>
                            <div class="col-md-6">
                                <input class="form-control" type="text" placeholder="Логин" name="login" value="{{ $user->login }}">
                            </div>
                        </div>
                        <div class="row py-1">
                            <div class="col-md-6">
                                <label>Email</label>
                            </div>
                            <div class="col-md-6">
                                <input class="form-control" type="email" placeholder="Email" name="email" value="{{ $user->email }}">
                            </div>
                        </div>
                        <div class="row py-1">
                            <div class="col-md-6">
                                <label>Телефон</label>
                            </div>
                            <div class="col-md-6">
                                <input class="form-control" type="text" placeholder="Телефон" name="phone" value="{{ $user->phone }}">
                            </div>
                        </div>
                        <div class="row py-1">
                            <div class="col-md-6">
                                <label>Город</label>
                            </div>
                            <div class="col-md-6">
                                <input class="form-control" type="text" placeholder="Город" name="city" value="{{ $userInfo->city }}">
                            </div>
                        </div>
                        <div class="row py-1">
                            <div class="col-md-6">
                                <label>Адрес</label>
                            </div>
                            <div class="col-md-6">
                                <input class="form-control" type="text" placeholder="Адрес" name="address" value="{{ $userInfo->address }}">
                            </div>
                        </div>
                        <div class="row py-1">
                            <div class="col-md-6">
                                <label>Время работы</label>
                            </div>
                            <div class="col-md-6">
                                <input class="form-control" type="text" placeholder="Время работы" name="work_time" value="{{ $userInfo->work_time }}">
                            </div>
                        </div>
                        <div class="row py-1">
                            <div class="col-md-6">
                                <label>Контакт</label>
                            </div>
                            <div class="col-md-6">
                                <input class="form-control" type="text" placeholder="Контакт" name="contact" value="{{ $userInfo->contact }}">
                            </div>
                        </div>
                        <div class="row py-1">
                            <div class="col-md-6">
                                <label>Описание</label>
                            </div>
                            <div class="col-md-6">
                                <input class="form-control" type="text" placeholder="description" name="description" value="{{ $userInfo->description }}">
                            </div>
                        </div>
                        <div class="row py-1">
                            <div class="col-md-6">
                                <label>Vk</label>
                            </div>
                            <div class="col-md-6">
                                <input class="form-control" type="text" placeholder="vk" name="vk" value="{{ $userInfo->vk }}">
                            </div>
                        </div>
                        <div class="row py-1">
                            <div class="col-md-6">
                                <label>Facebook</label>
                            </div>
                            <div class="col-md-6">
                                <input class="form-control" type="text" placeholder="facebook" name="facebook" value="{{ $userInfo->facebook }}">
                            </div>
                        </div>
                        <div class="row py-1">
                            <div class="col-md-6">
                                <label>Instagram</label>
                            </div>
                            <div class="col-md-6">
                                <input class="form-control" type="text" placeholder="instagram" name="instagram" value="{{ $userInfo->instagram }}">
                            </div>
                        </div>
                        <div class="row py-1">
                            <div class="col-md-6">
                                <label>Одноклассники</label>
                            </div>
                            <div class="col-md-6">
                                <input class="form-control" type="text" placeholder="Одноклассники" name="odnoklassniki" value="{{ $userInfo->odnoklassniki }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>           
</div>
@endsection