@php($fasahMeta = old('fasah_meta', $invoice?->fasah_meta ?? []))
<section class="fasah-fields panel" aria-label="بيانات Fasah">
    <div class="section-heading">بيانات الإرسال إلى Fasah</div>
    <p>تُرسل الفاتورة الصادرة تلقائيًا. يمكن ترك هذه البيانات ناقصة عند حفظ مسودة.</p>
    <div class="fasah-grid">
        <label class="fasah-field">
            نوع الفاتورة
            <select class="field" id="fasah-type" name="fasah_type">
                <option value="">اختر النوع</option>
                @foreach(['general'=>'فاتورة عامة','billoflading'=>'بوليصة شحن','declaration'=>'بيان جمركي','custombroker'=>'مخلص جمركي','importer'=>'مستورد','shippingagent'=>'وكيل شحن'] as $value=>$label)
                    <option value="{{ $value }}" @selected(old('fasah_type', $invoice?->fasah_type) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('fasah_type')<small class="field-error">{{ $message }}</small>@enderror
        </label>
        <p class="fasah-general-hint" data-fasah-types="general">الفاتورة العامة تستخدم البريد الإلكتروني والجوال من بيانات العميل.</p>
        @if($invoice && $invoice->status === 'issued')@error('client_id')<p class="field-error" data-fasah-types="general">{{ $message }} @can('clients.edit')<a data-client-edit-link href="{{ route('clients.edit', $invoice->client_id) }}">تعديل بيانات العميل</a>@endcan</p>@enderror @endif
        <label class="fasah-field" data-fasah-types="billoflading">
            نوع الشحنة
            <select class="field" name="fasah_meta[shipment_type]">
                <option value="">اختر نوع الشحنة</option>
                @foreach($fasahLookups::SHIPMENT_TYPES as $code => $label)<option value="{{ $code }}" @selected((string) ($fasahMeta['shipment_type'] ?? '') === (string) $code)>{{ $label }} ({{ $code }})</option>@endforeach
            </select>
            @error('fasah_meta.shipment_type')<small class="field-error">{{ $message }}</small>@enderror
        </label>
        <label class="fasah-field" data-fasah-types="billoflading,declaration,custombroker,importer,shippingagent">
            رمز الميناء
            <select class="field" id="fasah-port-select" name="fasah_meta[port]">
                <option value="">اختر الميناء</option>
                @foreach($fasahLookups::PORTS as $code => $label)<option value="{{ $code }}" @selected((string) ($fasahMeta['port'] ?? '') === (string) $code)>{{ $label }} ({{ $code }})</option>@endforeach
                <option value="__other__" @selected(!empty($fasahMeta['port']) && !$fasahLookups::isKnownPort((string) $fasahMeta['port']))>رمز آخر</option>
            </select>
            <input class="field" id="fasah-port-other" type="text" inputmode="numeric" placeholder="أدخل رمز الميناء الآخر" value="{{ !empty($fasahMeta['port']) && !$fasahLookups::isKnownPort((string) $fasahMeta['port']) ? $fasahMeta['port'] : '' }}" hidden>
            <small class="fasah-warning" data-fasah-warning="port" hidden>هذا الرمز غير موجود في القائمة المرفقة؛ تأكد منه مع Fasah.</small>
            @error('fasah_meta.port')<small class="field-error">{{ $message }}</small>@enderror
        </label>
        <label class="fasah-field" data-fasah-types="declaration">
            نوع البيان الجمركي
            <select class="field" name="fasah_meta[declaration_type]">
                <option value="">اختر نوع البيان</option>
                @foreach($fasahLookups::DECLARATION_TYPES as $code => $label)<option value="{{ $code }}" @selected((string) ($fasahMeta['declaration_type'] ?? '') === (string) $code)>{{ $label }} ({{ $code }})</option>@endforeach
            </select>
            <small class="fasah-warning" data-fasah-warning="declaration_type" hidden>هذا الرمز موجود في جدول القيم لكنه غير مذكور في تعريف API؛ تأكد منه مع Fasah.</small>
            @error('fasah_meta.declaration_type')<small class="field-error">{{ $message }}</small>@enderror
        </label>
        <label class="fasah-field" data-fasah-types="custombroker">
            نوع الرخصة
            <select class="field" name="fasah_meta[license_type]">
                <option value="">اختر نوع الرخصة</option>
                @foreach($fasahLookups::LICENSE_TYPES as $code => $label)<option value="{{ $code }}" @selected((string) ($fasahMeta['license_type'] ?? '') === (string) $code)>{{ $label }} ({{ $code }})</option>@endforeach
            </select>
            @error('fasah_meta.license_type')<small class="field-error">{{ $message }}</small>@enderror
        </label>
        @foreach([
            'bill_of_lading'=>['رقم بوليصة الشحن','billoflading,declaration,custombroker,importer,shippingagent','text'],
            'doc_ref_no'=>['مرجع المستند','billoflading','text'],
            'carrier_manifest'=>['رقم بيان الناقل (بديل مرجع المستند)','billoflading','text'],
            'carrier_manifest_date'=>['تاريخ بيان الناقل','billoflading','date'],
            'declaration_number'=>['رقم البيان الجمركي','declaration','text'],
            'declaration_date'=>['تاريخ البيان الجمركي','declaration','date'],
            'customs_broker_license'=>['رخصة المخلص الجمركي','custombroker','text'],
            'importer_number'=>['رقم المستورد','importer','text'],
            'shipping_agent_number'=>['رقم وكيل الشحن','shippingagent','text'],
        ] as $key=>[$label,$types,$inputType])
            <label class="fasah-field" data-fasah-types="{{ $types }}">
                {{ $label }}
                <input class="field" type="{{ $inputType }}" name="fasah_meta[{{ $key }}]" value="{{ $fasahMeta[$key] ?? '' }}">
                @if($key === 'doc_ref_no')<small class="fasah-warning" data-fasah-warning="doc_ref_no" hidden>مرجع المستند عادةً 14 رقمًا؛ تأكد من قيمته.</small>@endif
                @error('fasah_meta.'.$key)<small class="field-error">{{ $message }}</small>@enderror
            </label>
        @endforeach
    </div>
    @if(!empty($fasahWarnings))<div class="fasah-warning" role="note"><strong>تحقق من القيم التالية قبل الإرسال:</strong><ul>@foreach($fasahWarnings as $warning)<li>{{ $warning }}</li>@endforeach</ul></div>@endif
</section>
