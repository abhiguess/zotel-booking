@extends('layouts.app')

@section('title', 'Inventory & Pricing — Zotel Demo Property')

@section('content')
<div x-data="inventoryApp()" x-init="fetchInventory()" x-cloak class="max-w-6xl mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('search') }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Back to Search
        </a>
        <h1 class="text-2xl font-bold text-[#1e2a4a]">Inventory & Pricing</h1>
    </div>

    {{-- Room Type Tabs --}}
    <div class="flex border-b border-gray-200 mb-4">
        <template x-for="rt in roomTypes" :key="rt.slug">
            <button @click="activeRoomTab = rt.slug; activePlanTab = rt.rate_plans?.[0]?.code || ''"
                class="px-4 py-2 text-sm font-medium border-b-2 transition -mb-px"
                :class="activeRoomTab === rt.slug ? 'border-[#1e2a4a] text-[#1e2a4a]' : 'border-transparent text-gray-500 hover:text-gray-700'">
                <span x-text="rt.name"></span>
            </button>
        </template>
    </div>

    {{-- Rate Plan Sub-tabs --}}
    <div class="flex gap-2 mb-6" x-show="activeRoomType?.rate_plans?.length > 0">
        <template x-for="plan in activeRoomType?.rate_plans || []" :key="plan.code">
            <button @click="activePlanTab = plan.code"
                class="px-3 py-1.5 text-xs font-medium rounded-full transition"
                :class="activePlanTab === plan.code ? 'bg-[#1e2a4a] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                <span x-text="plan.name"></span> (<span x-text="plan.code"></span>)
            </button>
        </template>
    </div>

    {{-- Loading --}}
    <div x-show="loading" class="text-center py-12">
        <div class="inline-block w-6 h-6 border-3 border-gray-300 border-t-[#1e2a4a] rounded-full animate-spin"></div>
    </div>

    {{-- Table --}}
    <div x-show="!loading" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="text-left px-4 py-3 font-medium">Date</th>
                        <th class="text-center px-4 py-3 font-medium">Avail.</th>
                        <template x-for="i in occupancyColumns" :key="i">
                            <th class="text-right px-4 py-3 font-medium">
                                <span x-text="i"></span>P
                            </th>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="day in activeDailyRates" :key="day.date">
                        <tr class="border-t border-gray-100 hover:bg-gray-50"
                            :class="day.available_rooms === 0 || day.is_blocked ? 'opacity-50' : ''">
                            <td class="px-4 py-2.5">
                                <span x-text="day.date"></span>
                                <span class="text-gray-400 ml-1 text-xs" x-text="day.day"></span>
                            </td>
                            <td class="text-center px-4 py-2.5">
                                <template x-if="day.is_blocked">
                                    <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-medium">Blocked</span>
                                </template>
                                <template x-if="!day.is_blocked && day.available_rooms === 0">
                                    <span class="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full font-medium">Sold Out</span>
                                </template>
                                <template x-if="!day.is_blocked && day.available_rooms > 0">
                                    <span class="font-medium"
                                        :class="day.available_rooms <= 2 ? 'text-orange-600' : 'text-green-600'"
                                        x-text="day.available_rooms + '/' + day.total_rooms"></span>
                                </template>
                            </td>
                            <template x-for="i in occupancyColumns" :key="'p' + i">
                                <td class="text-right px-4 py-2.5">
                                    <template x-if="getOccupancyPrice(day, i) !== null">
                                        <span>&#8377;<span x-text="getOccupancyPrice(day, i).toLocaleString('en-IN')"></span></span>
                                    </template>
                                    <template x-if="getOccupancyPrice(day, i) === null">
                                        <span class="text-gray-300">—</span>
                                    </template>
                                </td>
                            </template>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function inventoryApp() {
    return {
        loading: true,
        roomTypes: [],
        activeRoomTab: '',
        activePlanTab: '',

        get activeRoomType() {
            return this.roomTypes.find(r => r.slug === this.activeRoomTab) || null;
        },

        get activePlan() {
            if (!this.activeRoomType) return null;
            return this.activeRoomType.rate_plans.find(p => p.code === this.activePlanTab) || null;
        },

        get activeDailyRates() {
            return this.activePlan?.daily_rates || [];
        },

        get occupancyColumns() {
            const maxAdults = this.activeRoomType?.max_adults || 3;
            return Array.from({ length: maxAdults }, (_, i) => i + 1);
        },

        getOccupancyPrice(day, occupancy) {
            const rate = day.occupancy_rates?.find(r => r.occupancy === occupancy);
            return rate ? rate.price : null;
        },

        async fetchInventory() {
            try {
                const response = await fetch('/api/inventory', {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await response.json();

                if (data.success) {
                    this.roomTypes = data.data;
                    if (this.roomTypes.length > 0) {
                        this.activeRoomTab = this.roomTypes[0].slug;
                        this.activePlanTab = this.roomTypes[0].rate_plans?.[0]?.code || '';
                    }
                }
            } catch (e) {
                console.error('Failed to load inventory', e);
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
@endsection
