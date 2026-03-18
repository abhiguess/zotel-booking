@extends('layouts.app')

@section('title', 'Inventory & Pricing — Zotel Demo Property')

@section('content')
<div x-data="inventoryApp()" x-init="fetchInventory()" x-cloak class="max-w-5xl mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('search') }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Back to Search
        </a>
        <h1 class="text-2xl font-bold text-[#1e2a4a]">Inventory & Pricing</h1>
    </div>

    {{-- Tab Switcher --}}
    <div class="flex border-b border-gray-200 mb-6">
        <template x-for="rt in roomTypes" :key="rt.slug">
            <button @click="activeTab = rt.slug"
                class="px-4 py-2 text-sm font-medium border-b-2 transition -mb-px"
                :class="activeTab === rt.slug ? 'border-[#1e2a4a] text-[#1e2a4a]' : 'border-transparent text-gray-500 hover:text-gray-700'">
                <span x-text="rt.name"></span>
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
                        <th class="text-right px-4 py-3 font-medium">1 Person</th>
                        <th class="text-right px-4 py-3 font-medium">2 Persons</th>
                        <th class="text-right px-4 py-3 font-medium">3 Persons</th>
                        <th class="text-right px-4 py-3 font-medium">Breakfast</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="day in activeInventory" :key="day.date">
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
                            <td class="text-right px-4 py-2.5">&#8377;<span x-text="day.price_1p.toLocaleString('en-IN')"></span></td>
                            <td class="text-right px-4 py-2.5">&#8377;<span x-text="day.price_2p.toLocaleString('en-IN')"></span></td>
                            <td class="text-right px-4 py-2.5">&#8377;<span x-text="day.price_3p.toLocaleString('en-IN')"></span></td>
                            <td class="text-right px-4 py-2.5">&#8377;<span x-text="day.breakfast_price_pp.toLocaleString('en-IN')"></span> <span class="text-gray-400 text-xs">pp</span></td>
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
        activeTab: '',

        get activeInventory() {
            const rt = this.roomTypes.find(r => r.slug === this.activeTab);
            return rt ? rt.inventory : [];
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
                        this.activeTab = this.roomTypes[0].slug;
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
