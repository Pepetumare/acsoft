@extends('layouts.gestion')
@section('title', 'Analítica | ' . $negocio->nombre)
@section('content')
<div class="container-fluid p-3 p-lg-5 analytics-page">
    <div class="gestion-page-header analytics-header"><div><span class="section-eyebrow">{{ $negocio->nombre }}</span><h1>Analítica</h1><p>Métricas claras para entender el rendimiento de tu negocio.</p></div><span class="analytics-date-badge">{{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y') }}</span></div>
    <form method="GET" action="{{ route('gestion.analitica', $negocio) }}" class="analytics-filter-card mb-4">
        <div class="analytics-periods" role="group" aria-label="Período de analítica">
            @foreach(['hoy'=>'Hoy','ultimos_7_dias'=>'Últimos 7 días','ultimos_30_dias'=>'Últimos 30 días','este_mes'=>'Este mes','mes_anterior'=>'Mes anterior','personalizado'=>'Personalizado'] as $value=>$label)
                <button type="submit" name="periodo" value="{{ $value }}" class="analytics-period {{ $periodo === $value ? 'active' : '' }}">{{ $label }}</button>
            @endforeach
        </div>
        <div class="analytics-custom-dates {{ $periodo === 'personalizado' || $errors->any() ? '' : 'd-none' }}" id="custom-dates">
            <input type="hidden" name="periodo" value="personalizado" disabled id="custom-period-input">
            <div><label for="desde">Desde</label><input class="form-control @error('desde') is-invalid @enderror" type="date" id="desde" name="desde" value="{{ old('desde', $desde->toDateString()) }}"></div>
            <div><label for="hasta">Hasta</label><input class="form-control @error('hasta') is-invalid @enderror" type="date" id="hasta" name="hasta" value="{{ old('hasta', $hasta->toDateString()) }}"></div>
            <button class="btn btn-acsoft-primary" type="submit">Aplicar</button><a class="btn btn-acsoft-outline" href="{{ route('gestion.analitica', $negocio) }}">Limpiar</a>
        </div>
        @if($errors->any())<div class="alert alert-danger mt-3 mb-0" role="alert">{{ $errors->first() }}</div>@endif
    </form>
    <div class="analytics-kpis mb-4">
        @foreach([['Ventas totales','$'.number_format($kpis['ventas_total'],0,',','.'),'sales'],['Gastos totales','$'.number_format($kpis['gastos_total'],0,',','.'),'expenses'],['Resultado estimado','$'.number_format($kpis['resultado'],0,',','.'),$kpis['resultado']>=0?'result':'negative'],['Ticket promedio','$'.number_format($kpis['ticket_promedio'],0,',','.'),'ticket'],['Cantidad de ventas',number_format($kpis['cantidad_ventas'],0,',','.'),'count'],['Productos vendidos',number_format($kpis['productos_vendidos'],3,',','.'),'products']] as [$label,$value,$tone])
            <article class="analytics-kpi analytics-kpi-{{ $tone }}"><span>{{ $label }}</span><strong>{{ $value }}</strong><small>En el período seleccionado</small></article>
        @endforeach
    </div>
    @unless($hasActivity)<div class="analytics-empty mb-4"><strong>Aún no hay datos para este período</strong><span>Prueba seleccionando otro rango o registra una venta o gasto.</span></div>@endunless
    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-8"><x-analytics-chart id="timeline-chart" eyebrow="Evolución" title="Ventas vs gastos" :has-data="array_sum($charts['timeline']['saleValues']) > 0 || array_sum($charts['timeline']['expenseValues']) > 0" wide /></div>
        <div class="col-12 col-xl-4"><x-analytics-chart id="payments-chart" eyebrow="Distribución" title="Ventas por método de pago" :has-data="array_sum($charts['payments']['totals']) > 0" /></div>
        <div class="col-12 col-lg-6"><x-analytics-chart id="products-chart" eyebrow="Top 10" title="Productos más vendidos" :has-data="array_sum($charts['products']['quantities']) > 0" /></div>
        <div class="col-12 col-lg-6"><x-analytics-chart id="weekdays-chart" eyebrow="Comportamiento semanal" title="Ventas por día de la semana" :has-data="array_sum($charts['weekdays']['totals']) > 0" /></div>
    </div>
    <div class="row g-4">
        <div class="col-12 col-xl-7"><section class="analytics-panel h-100"><div class="analytics-panel-heading"><div><span>Inventario</span><h2>Stock crítico</h2></div><small>Stock actual ≤ mínimo</small></div><div class="table-responsive"><table class="table analytics-table mb-0"><thead><tr><th>Producto</th><th>Stock actual</th><th>Stock mínimo</th><th>Estado</th></tr></thead><tbody>@forelse($stockCritico as $producto)<tr><td><strong>{{ $producto->nombre }}</strong></td><td>{{ number_format($producto->stockActual(),3,',','.') }}</td><td>{{ number_format((float)$producto->stock_minimo,3,',','.') }}</td><td><span class="analytics-status {{ $producto->stockActual()<=0?'danger':'warning' }}">{{ $producto->stockActual()<=0?'Sin stock':'Bajo' }}</span></td></tr>@empty<tr><td colspan="4" class="analytics-table-empty">No hay productos con stock crítico.</td></tr>@endforelse</tbody></table></div></section></div>
        <div class="col-12 col-xl-5"><section class="analytics-panel h-100"><div class="analytics-panel-heading"><div><span>Resumen inteligente</span><h2>Insights del período</h2></div></div><ul class="analytics-insights">@foreach($insights as $insight)<li><span>i</span><p>{{ $insight }}</p></li>@endforeach</ul></section></div>
    </div>
</div>
@endsection
@push('scripts')
<script type="module">
const Chart=window.Chart; const data=@json($charts); const money=value=>new Intl.NumberFormat('es-CL',{style:'currency',currency:'CLP',maximumFractionDigits:0}).format(value);
const emptyPlugin={id:'emptyState',afterDraw(chart){if(chart.data.datasets.some(set=>set.data.some(Number)))return;const {ctx,chartArea}=chart;ctx.save();ctx.textAlign='center';ctx.fillStyle='#657084';ctx.font='14px system-ui';ctx.fillText('Aún no hay datos para este período',(chartArea.left+chartArea.right)/2,(chartArea.top+chartArea.bottom)/2);ctx.restore()}};
Chart.defaults.color='#657084'; Chart.defaults.font.family='Inter, system-ui, sans-serif';
const common={responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{usePointStyle:true,boxWidth:8}},tooltip:{callbacks:{label:ctx=>`${ctx.dataset.label||ctx.label}: ${money(ctx.parsed.y??ctx.parsed)}`}}},scales:{x:{grid:{display:false}},y:{beginAtZero:true,grid:{color:'#edf1f5'},ticks:{callback:money}}}};
const renderChart=(id,config)=>{const canvas=document.getElementById(id);if(canvas)new Chart(canvas,config)};
renderChart('timeline-chart',{type:'line',plugins:[emptyPlugin],data:{labels:data.timeline.labels,datasets:[{label:'Ventas',data:data.timeline.saleValues,borderColor:'#2497e3',backgroundColor:'rgba(36,151,227,.12)',fill:true,tension:.3},{label:'Gastos',data:data.timeline.expenseValues,borderColor:'#ef6a6a',backgroundColor:'rgba(239,106,106,.08)',fill:true,tension:.3}]},options:common});
renderChart('payments-chart',{type:'doughnut',plugins:[emptyPlugin],data:{labels:data.payments.labels,datasets:[{label:'Total',data:data.payments.totals,backgroundColor:['#2497e3','#28a873','#f2b84b','#8d72e1','#ef6a6a','#8aa0b8'],borderWidth:0}]},options:{responsive:true,maintainAspectRatio:false,cutout:'67%',plugins:common.plugins}});
const productOptions={...common,plugins:{...common.plugins,tooltip:{callbacks:{label:ctx=>[`Cantidad: ${ctx.parsed.y}`,`Vendido: ${money(data.products.totals[ctx.dataIndex])}`]}}}};
renderChart('products-chart',{type:'bar',plugins:[emptyPlugin],data:{labels:data.products.labels,datasets:[{label:'Cantidad vendida',data:data.products.quantities,backgroundColor:'#2497e3',borderRadius:6}]},options:productOptions});
renderChart('weekdays-chart',{type:'bar',plugins:[emptyPlugin],data:{labels:data.weekdays.labels,datasets:[{label:'Total vendido',data:data.weekdays.totals,backgroundColor:'#28a873',borderRadius:6}]},options:common});
const button=document.querySelector('button[value="personalizado"]'),dates=document.getElementById('custom-dates'),period=document.getElementById('custom-period-input');button?.addEventListener('click',event=>{event.preventDefault();dates.classList.remove('d-none');period.disabled=false;document.getElementById('desde').focus()});if(!dates.classList.contains('d-none'))period.disabled=false;
</script>
@endpush
