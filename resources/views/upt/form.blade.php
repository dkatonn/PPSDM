@extends('layouts.app')
@section('title',$upt->exists?'Edit UPT':'Tambah UPT')
@section('breadcrumb','Data UPT')
@push('styles')@unless(config('services.google_maps.browser_key'))<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">@endunless<style>#upt-picker{height:360px;border-radius:10px;z-index:1}.google-place-search{display:block;width:100%;max-width:720px}.google-place-search::part(input){min-height:42px;border:1px solid #ced4da;border-radius:7px;padding:8px 12px;font:inherit}</style>@endpush
@section('content')
<div class="mb-4"><a href="{{ route('upt.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Kembali ke UPT</a><h1 class="page-title h3 mt-2">{{ $upt->exists?'Edit UPT':'Tambah UPT' }}</h1></div>
@include('partials.feedback')
<form method="POST" action="{{ $upt->exists?route('upt.update',$upt):route('upt.store') }}" class="card p-4">@csrf @if($upt->exists)@method('PUT')@endif
<div class="row g-3">
    <div class="col-md-8"><label class="form-label">Nama UPT *</label><input required name="name" class="form-control" value="{{ old('name',$upt->name) }}"></div>
    <div class="col-md-4"><label class="form-label">Jenis UPT</label><input name="type" class="form-control" value="{{ old('type',$upt->type) }}"></div>
    <div class="col-md-6"><label class="form-label">Wilayah kerja</label><input name="work_area" class="form-control" value="{{ old('work_area',$upt->work_area) }}"></div>
    <div class="col-md-3"><label class="form-label">Provinsi *</label><input required name="province" class="form-control" value="{{ old('province',$upt->province) }}"></div>
    <div class="col-md-3"><label class="form-label">Kabupaten/Kota</label><input name="city" class="form-control" value="{{ old('city',$upt->city) }}"></div>
    <div class="col-md-8"><label class="form-label">Alamat</label><textarea id="address" name="address" class="form-control" rows="2">{{ old('address',$upt->address) }}</textarea></div>
    <div class="col-md-4"><label class="form-label">Kontak</label><input name="contact" class="form-control" value="{{ old('contact',$upt->contact) }}"></div>
    <div class="col-md-4"><label class="form-label">Status operasional *</label><select name="status" class="form-select"><option value="operasional" @selected(old('status',$upt->status??'operasional')==='operasional')>Operasional</option><option value="non_operasional" @selected(old('status',$upt->status)==='non_operasional')>Non-operasional</option></select></div>
    <div class="col-12"><div class="d-flex justify-content-between align-items-end flex-wrap gap-2"><div><label class="form-label fw-semibold mb-1">Lokasi UPT di peta Indonesia *</label><div class="small muted">Cari alamat/nama kantor dengan Google Maps, pilih hasilnya untuk mengisi koordinat. Pin juga tetap bisa dipindahkan manual.</div></div></div>@if(config('services.google_maps.browser_key'))<div id="google-place-search" class="mt-2"></div>@else<div class="alert alert-warning py-2 mt-2 mb-0 small">Pencarian Google Maps belum aktif. Isi <code>GOOGLE_MAPS_BROWSER_KEY</code> pada <code>.env</code> dan aktifkan Maps JavaScript API serta Places API (New) pada Google Cloud.</div>@endif<div id="upt-picker" class="mt-3"></div></div>
    <div class="col-md-6"><label class="form-label">Latitude *</label><input id="latitude" name="latitude" type="number" step="0.0000001" min="-11.2" max="6.3" required class="form-control" value="{{ old('latitude',$upt->latitude) }}"></div>
    <div class="col-md-6"><label class="form-label">Longitude *</label><input id="longitude" name="longitude" type="number" step="0.0000001" min="95" max="141.1" required class="form-control" value="{{ old('longitude',$upt->longitude) }}"></div>
    <div class="col-12"><label class="form-label">Keterangan</label><textarea name="notes" class="form-control" rows="2">{{ old('notes',$upt->notes) }}</textarea></div>
</div><div class="text-end mt-4"><button class="btn btn-primary">Simpan UPT</button></div></form>
@endsection
@push('scripts')
@if(config('services.google_maps.browser_key'))
<script>
const latInput = document.getElementById('latitude');
const lngInput = document.getElementById('longitude');
const addressInput = document.getElementById('address');
window.initGoogleUptMap = async function () {
    const { Map } = await google.maps.importLibrary('maps');
    const center = latInput.value && lngInput.value
        ? { lat: Number(latInput.value), lng: Number(lngInput.value) }
        : { lat: -2.5, lng: 118 };
    const map = new Map(document.getElementById('upt-picker'), {
        center, zoom: latInput.value ? 15 : 5, mapTypeControl: true,
        streetViewControl: false, fullscreenControl: true,
        restriction: { latLngBounds: { north: 6.5, south: -11.5, east: 141.5, west: 94.5 }, strictBounds: false }
    });
    let marker = null;
    function setLocation(lat, lng, moveMap = true) {
        lat = Number(lat); lng = Number(lng);
        latInput.value = lat.toFixed(7); lngInput.value = lng.toFixed(7);
        const position = { lat, lng };
        if (marker) marker.setPosition(position);
        else {
            marker = new google.maps.Marker({ map, position, draggable: true, title: 'Lokasi UPT' });
            marker.addListener('dragend', event => { latInput.value = event.latLng.lat().toFixed(7); lngInput.value = event.latLng.lng().toFixed(7); });
        }
        if (moveMap) { map.setCenter(position); map.setZoom(16); }
    }
    map.addListener('click', event => setLocation(event.latLng.lat(), event.latLng.lng(), false));
    if (latInput.value && lngInput.value) {
        setLocation(center.lat, center.lng, false);
    }
    latInput.addEventListener('change', () => { if (latInput.value && lngInput.value) setLocation(latInput.value, lngInput.value); });
    lngInput.addEventListener('change', () => { if (latInput.value && lngInput.value) setLocation(latInput.value, lngInput.value); });
    const { PlaceAutocompleteElement } = await google.maps.importLibrary('places');
    const search = new PlaceAutocompleteElement();
    search.className = 'google-place-search';
    search.placeholder = 'Cari nama kantor UPT atau alamat di Indonesia';
    search.includedRegionCodes = ['id'];
    document.getElementById('google-place-search').append(search);
    search.addEventListener('gmp-select', async ({ placePrediction }) => {
        const place = placePrediction.toPlace();
        await place.fetchFields({ fields: ['location', 'formattedAddress'] });
        if (!place.location) return;
        setLocation(place.location.lat(), place.location.lng());
        addressInput.value = place.formattedAddress || addressInput.value;
    });
};
</script>
<script async src="https://maps.googleapis.com/maps/api/js?key={{ urlencode(config('services.google_maps.browser_key')) }}&callback=initGoogleUptMap&loading=async"></script>
@else
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
<script>
const picker = L.map('upt-picker').setView([-2.5, 118], 4);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'}).addTo(picker);
const latInput = document.getElementById('latitude'); const lngInput = document.getElementById('longitude');
let marker = null;
function selectLocation(lat, lng) { latInput.value = Number(lat).toFixed(7); lngInput.value = Number(lng).toFixed(7); if (marker) marker.setLatLng([lat, lng]); else marker = L.marker([lat, lng]).addTo(picker); }
picker.on('click', event => selectLocation(event.latlng.lat, event.latlng.lng));
function updateFromFields() { const lat = Number(latInput.value); const lng = Number(lngInput.value); if (latInput.value && lngInput.value && Number.isFinite(lat) && Number.isFinite(lng)) { selectLocation(lat, lng); picker.setView([lat, lng], Math.max(picker.getZoom(), 10)); } }
latInput.addEventListener('change', updateFromFields); lngInput.addEventListener('change', updateFromFields);
if (latInput.value && lngInput.value) updateFromFields();
setTimeout(() => picker.invalidateSize(), 100);
</script>
@endif
@endpush
