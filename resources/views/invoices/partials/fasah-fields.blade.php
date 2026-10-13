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
        </label>
        <p class="fasah-general-hint" data-fasah-types="general">الفاتورة العامة تستخدم البريد الإلكتروني والجوال من بيانات العميل.</p>
        @foreach([
            'bill_of_lading'=>['رقم بوليصة الشحن','billoflading,declaration,custombroker,importer,shippingagent','text'],
            'shipment_type'=>['نوع الشحنة (رقم Fasah)','billoflading','number'],
            'port'=>['رمز الميناء','billoflading,declaration,custombroker,importer,shippingagent','text'],
            'doc_ref_no'=>['مرجع المستند','billoflading','text'],
            'carrier_manifest'=>['رقم بيان الناقل (بديل مرجع المستند)','billoflading','text'],
            'carrier_manifest_date'=>['تاريخ بيان الناقل','billoflading','date'],
            'declaration_number'=>['رقم البيان الجمركي','declaration','text'],
            'declaration_date'=>['تاريخ البيان الجمركي','declaration','date'],
            'declaration_type'=>['نوع البيان (رقم Fasah)','declaration','number'],
            'customs_broker_license'=>['رخصة المخلص الجمركي','custombroker','text'],
            'license_type'=>['نوع الرخصة','custombroker','text'],
            'importer_number'=>['رقم المستورد','importer','text'],
            'shipping_agent_number'=>['رقم وكيل الشحن','shippingagent','text'],
        ] as $key=>[$label,$types,$inputType])
            <label class="fasah-field" data-fasah-types="{{ $types }}">
                {{ $label }}
                <input class="field" type="{{ $inputType }}" name="fasah_meta[{{ $key }}]" value="{{ $fasahMeta[$key] ?? '' }}">
            </label>
        @endforeach
    </div>
</section>
