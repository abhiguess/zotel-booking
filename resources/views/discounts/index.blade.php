@extends('layouts.app')

@section('title', 'Discount Configuration — Zotel Demo Property')

@section('content')
<div x-data="discountApp()" x-init="fetchDiscounts()" x-cloak class="max-w-3xl mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('search') }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Back to Search
        </a>
        <h1 class="text-2xl font-bold text-[#1e2a4a]">Discount Configuration</h1>
    </div>

    {{-- Loading --}}
    <div x-show="loading" class="text-center py-12">
        <div class="inline-block w-6 h-6 border-3 border-gray-300 border-t-[#1e2a4a] rounded-full animate-spin"></div>
    </div>

    <div x-show="!loading" class="space-y-8">
        {{-- Long Stay Discounts --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-[#1e2a4a] mb-1">Long Stay Discounts</h2>
            <p class="text-sm text-gray-500 mb-4">Applied when the stay meets minimum night thresholds.</p>
            <div class="space-y-3">
                <template x-for="rule in longStay" :key="rule.id">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-bold text-green-700 bg-green-100 px-2.5 py-1 rounded">
                                <span x-text="rule.discount_percentage"></span>% OFF
                            </span>
                            <span class="text-sm text-gray-700">
                                <span x-text="rule.min_nights"></span>+ nights
                            </span>
                        </div>
                        <span class="text-xs text-gray-400" x-text="rule.name"></span>
                    </div>
                </template>
                <template x-if="longStay.length === 0">
                    <p class="text-sm text-gray-400">No long stay discounts configured.</p>
                </template>
            </div>
        </div>

        {{-- Last Minute Discounts --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-[#1e2a4a] mb-1">Last Minute Discounts</h2>
            <p class="text-sm text-gray-500 mb-4">Applied when check-in is within a specified number of days from today.</p>
            <div class="space-y-3">
                <template x-for="rule in lastMinute" :key="rule.id">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-bold text-green-700 bg-green-100 px-2.5 py-1 rounded">
                                <span x-text="rule.discount_percentage"></span>% OFF
                            </span>
                            <span class="text-sm text-gray-700">
                                Check-in within <span x-text="rule.within_days"></span> days
                            </span>
                        </div>
                        <span class="text-xs text-gray-400" x-text="rule.name"></span>
                    </div>
                </template>
                <template x-if="lastMinute.length === 0">
                    <p class="text-sm text-gray-400">No last minute discounts configured.</p>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function discountApp() {
    return {
        loading: true,
        longStay: [],
        lastMinute: [],

        async fetchDiscounts() {
            try {
                const response = await fetch('/api/discounts', {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await response.json();

                if (data.success) {
                    this.longStay = data.data.long_stay || [];
                    this.lastMinute = data.data.last_minute || [];
                }
            } catch (e) {
                console.error('Failed to load discounts', e);
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
@endsection
