<section class="customer panel" aria-label="بيانات العميل">
    <div class="customer-row">
        <label for="method">الطريقة:</label>
        <select class="field" id="method" name="delivery_method">
            <option @selected(old('delivery_method',$invoice?->delivery_method) === 'طباعة')>طباعة</option>
            <option @selected(old('delivery_method',$invoice?->delivery_method) === 'بريد إلكتروني')>بريد إلكتروني</option>
        </select>
    </div>

    <div class="customer-row">
        <label for="client">العميل <b class="required">*</b></label>
        <select class="field" id="client" name="client_id">
            <option value="">(اختر عميل)</option>
            @foreach($clients as $client)<option value="{{ $client->id }}" @selected(old('client_id',$invoice?->client_id ?? request('client_id')) == $client->id)>{{ $client->business_name }}</option>@endforeach
        </select>
        @error('client_id')<small class="field-error">{{ $message }} @can('clients.edit')@if(old('client_id', $invoice?->client_id))<a href="{{ route('clients.edit', old('client_id', $invoice?->client_id)) }}">تعديل العميل</a>@endif @endcan</small>@enderror
    </div>

    <div class="customer-tools">
        @can('clients.create')<a class="btn blue small" id="new-client" href="{{ route('clients.create') }}">⊕ جديد</a>@endcan
        <button class="currency" id="currency" type="button" disabled>SAR</button>
    </div>
</section>
