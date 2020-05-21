@extends('layouts.mainApp')

@section('content')
<div class="form-group{{ $errors->has('department') ? ' has-error' : '' }}">
    <label for="department" class="col-md-4 control-label">Статус</label>

    <div class="col-md-6">
        <select id="status" class="form-control" name="status" required>
            <option value="free" @if($info->status == "free") selected @endif>Свободен</option>
            <option value="busy" @if($info->status == "busy") selected @endif>Занят</option>
            <option value="offline" @if($info->status == "offline") selected @endif>Не в сети</option>
        </select>
    </div>
</div>
@endsection