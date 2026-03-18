@extends('layouts.app')

@section('title', 'Search — Zotel Demo Property')

@section('content')
<div x-data="searchApp()" x-cloak>
    {{-- Sticky Search Bar --}}
    <div class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 py-3">
            <form @submit.prevent="performSearch" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[140px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Check-in</label>
                    <input type="date" x-model="form.check_in" :min="today"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Check-out</label>
                    <input type="date" x-model="form.check_out" :min="minCheckOut"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="min-w-[120px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Adults</label>
                    <div class="flex items-center border border-gray-300 rounded-lg">
                        <button type="button" @click="decrementAdults"
                            class="px-3 py-2 text-gray-500 hover:text-gray-700 disabled:opacity-30"
                            :disabled="form.adults <= 1">-</button>
                        <span class="px-3 py-2 text-sm font-medium" x-text="form.adults"></span>
                        <button type="button" @click="incrementAdults"
                            class="px-3 py-2 text-gray-500 hover:text-gray-700 disabled:opacity-30"
                            :disabled="form.adults >= 3">+</button>
                    </div>
                </div>
                <button type="submit" :disabled="loading"
                    class="bg-[#1e2a4a] text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-[#2a3a5c] disabled:opacity-50 transition">
                    <span x-show="!loading">Search</span>
                    <span x-show="loading">Searching...</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Default State (before search) --}}
    <div x-show="!hasSearched && !loading" class="flex items-center justify-center" style="min-height: calc(100vh - 72px)">
        <div class="text-center max-w-lg px-4">
            <h1 class="text-3xl font-bold text-[#1e2a4a] mb-3">Zotel Demo Property</h1>
            <p class="text-gray-500 mb-8">Select your dates and number of guests, then search to see available rooms with transparent pricing.</p>
            <div class="flex justify-center gap-4">
                <a href="{{ route('discounts') }}"
                    class="border border-gray-300 text-gray-700 px-5 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    View Discounts
                </a>
                <a href="{{ route('inventory') }}"
                    class="border border-gray-300 text-gray-700 px-5 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    View Inventory
                </a>
            </div>
        </div>
    </div>

    {{-- Loading State --}}
    <div x-show="loading" class="flex items-center justify-center" style="min-height: calc(100vh - 72px)">
        <div class="text-center">
            <div class="inline-block w-8 h-8 border-4 border-gray-300 border-t-[#1e2a4a] rounded-full animate-spin"></div>
            <p class="mt-3 text-gray-500 text-sm">Searching available rooms...</p>
        </div>
    </div>

    {{-- Error State --}}
    <div x-show="error && !loading" class="max-w-5xl mx-auto px-4 py-8">
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-red-700 text-sm" x-text="error"></p>
            <template x-if="validationErrors">
                <ul class="mt-2 text-red-600 text-sm list-disc list-inside">
                    <template x-for="(messages, field) in validationErrors" :key="field">
                        <template x-for="msg in messages" :key="msg">
                            <li x-text="msg"></li>
                        </template>
                    </template>
                </ul>
            </template>
        </div>
    </div>

    {{-- Results State --}}
    <div x-show="hasSearched && !loading && !error" class="max-w-5xl mx-auto px-4 py-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-[#1e2a4a]">Select your stay at Zotel Demo Property</h2>
            <p class="text-gray-500 mt-1 text-sm">
                <span x-text="results?.search_params?.nights"></span> nights &middot;
                <span x-text="results?.search_params?.adults"></span> <span x-text="results?.search_params?.adults === 1 ? 'adult' : 'adults'"></span>
                <template x-if="results?.discount?.applied">
                    <span> &middot; <span class="text-green-600 font-medium" x-text="results.discount.name"></span></span>
                </template>
            </p>
        </div>

        {{-- Room Cards --}}
        <div class="space-y-6">
            <template x-for="room in results?.room_types || []" :key="room.id">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden relative">
                    {{-- Sold Out Overlay --}}
                    <div x-show="!room.is_available"
                        class="absolute inset-0 bg-gray-100/80 z-10 flex items-center justify-center">
                        <span class="bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-bold uppercase tracking-wide">Sold Out</span>
                    </div>

                    <div class="md:flex">
                        {{-- Room Image Placeholder --}}
                        <div class="md:w-64 h-48 md:h-auto bg-gradient-to-br flex items-center justify-center shrink-0"
                            :class="room.slug === 'standard' ? 'from-blue-100 to-blue-200' : 'from-amber-100 to-amber-200'">
                            <div class="text-center p-4">
                                <svg class="w-12 h-12 mx-auto mb-2" :class="room.slug === 'standard' ? 'text-blue-400' : 'text-amber-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2 20V8l2-2h16l2 2v12M2 14h20M6 14v-2a2 2 0 012-2h8a2 2 0 012 2v2"></path>
                                </svg>
                                <span class="text-sm font-medium" :class="room.slug === 'standard' ? 'text-blue-500' : 'text-amber-500'" x-text="room.name"></span>
                            </div>
                        </div>

                        {{-- Room Details --}}
                        <div class="flex-1 p-5">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h3 class="text-lg font-semibold text-[#1e2a4a]" x-text="room.name"></h3>
                                    <p class="text-gray-500 text-sm mt-0.5" x-text="room.description"></p>
                                </div>
                                <template x-if="room.is_available && room.available_rooms <= 2">
                                    <span class="text-xs font-semibold text-orange-600 bg-orange-50 px-2 py-1 rounded-full whitespace-nowrap">
                                        Only <span x-text="room.available_rooms"></span> room<span x-text="room.available_rooms === 1 ? '' : 's'"></span> left
                                    </span>
                                </template>
                            </div>

                            {{-- Amenities --}}
                            <div class="flex flex-wrap gap-1.5 mb-4">
                                <template x-for="amenity in room.amenities || []" :key="amenity">
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded" x-text="amenity"></span>
                                </template>
                            </div>

                            {{-- Pricing Rows (only if available) --}}
                            <template x-if="room.is_available && room.pricing">
                                <div class="space-y-3">
                                    {{-- Room Only --}}
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <span class="text-sm font-medium text-gray-700">Room Only</span>
                                            <template x-if="room.pricing.room_only.discount_percentage > 0">
                                                <span class="ml-2 text-xs font-bold text-green-700 bg-green-100 px-1.5 py-0.5 rounded">
                                                    -<span x-text="room.pricing.room_only.discount_percentage"></span>%
                                                </span>
                                            </template>
                                        </div>
                                        <div class="text-right">
                                            <template x-if="room.pricing.room_only.discount_amount > 0">
                                                <span class="text-sm text-gray-400 line-through mr-2">
                                                    &#8377;<span x-text="formatCurrency(room.pricing.room_only.base_total)"></span>
                                                </span>
                                            </template>
                                            <span class="text-lg font-bold text-[#1e2a4a]">
                                                &#8377;<span x-text="formatCurrency(room.pricing.room_only.final_total)"></span>
                                            </span>
                                            <span class="text-xs text-gray-400 ml-1">per <span x-text="results.search_params.nights"></span> nights</span>
                                            <button @click="selectRoom(room, 'room_only')"
                                                class="ml-3 bg-[#1e2a4a] text-white px-4 py-1.5 rounded-lg text-xs font-medium hover:bg-[#2a3a5c] transition">
                                                Select
                                            </button>
                                        </div>
                                    </div>

                                    {{-- With Breakfast --}}
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <span class="text-sm font-medium text-gray-700">With Breakfast</span>
                                            <template x-if="room.pricing.with_breakfast.discount_percentage > 0">
                                                <span class="ml-2 text-xs font-bold text-green-700 bg-green-100 px-1.5 py-0.5 rounded">
                                                    -<span x-text="room.pricing.with_breakfast.discount_percentage"></span>%
                                                </span>
                                            </template>
                                        </div>
                                        <div class="text-right">
                                            <template x-if="room.pricing.with_breakfast.discount_amount > 0">
                                                <span class="text-sm text-gray-400 line-through mr-2">
                                                    &#8377;<span x-text="formatCurrency(room.pricing.with_breakfast.base_total)"></span>
                                                </span>
                                            </template>
                                            <span class="text-lg font-bold text-[#1e2a4a]">
                                                &#8377;<span x-text="formatCurrency(room.pricing.with_breakfast.final_total)"></span>
                                            </span>
                                            <span class="text-xs text-gray-400 ml-1">per <span x-text="results.search_params.nights"></span> nights</span>
                                            <button @click="selectRoom(room, 'with_breakfast')"
                                                class="ml-3 bg-[#1e2a4a] text-white px-4 py-1.5 rounded-lg text-xs font-medium hover:bg-[#2a3a5c] transition">
                                                Select
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Nightly Breakdown Toggle --}}
                                    <div>
                                        <button @click="room._showBreakdown = !room._showBreakdown"
                                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                            <span x-text="room._showBreakdown ? 'Hide' : 'Show'"></span> nightly breakdown
                                        </button>
                                        <div x-show="room._showBreakdown" x-transition class="mt-2">
                                            <table class="w-full text-xs">
                                                <thead>
                                                    <tr class="text-gray-400 border-b">
                                                        <th class="text-left py-1 font-medium">Date</th>
                                                        <th class="text-right py-1 font-medium">Room Rate</th>
                                                        <th class="text-right py-1 font-medium">Breakfast</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template x-for="night in room.nightly_breakdown" :key="night.date">
                                                        <tr class="border-b border-gray-100">
                                                            <td class="py-1.5">
                                                                <span x-text="night.date"></span>
                                                                <span class="text-gray-400 ml-1" x-text="night.day"></span>
                                                            </td>
                                                            <td class="text-right py-1.5">&#8377;<span x-text="formatCurrency(night.room_rate)"></span></td>
                                                            <td class="text-right py-1.5">&#8377;<span x-text="formatCurrency(night.breakfast_total)"></span></td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Sticky Summary Bar --}}
    <div x-show="selectedRoom" x-transition
        class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-50">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-[#1e2a4a]">Stay Summary</p>
                <p class="text-xs text-gray-500">
                    <span x-text="selectedRoom?.name"></span> &middot;
                    <span x-text="selectedPlan === 'room_only' ? 'Room Only' : 'With Breakfast'"></span> &middot;
                    <span x-text="form.check_in"></span> to <span x-text="form.check_out"></span> &middot;
                    <span x-text="form.adults"></span> <span x-text="form.adults === 1 ? 'adult' : 'adults'"></span>
                    <template x-if="selectedPricing?.discount_amount > 0">
                        <span class="text-green-600"> &middot; Save &#8377;<span x-text="formatCurrency(selectedPricing.discount_amount)"></span></span>
                    </template>
                </p>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-xl font-bold text-[#1e2a4a]">&#8377;<span x-text="formatCurrency(selectedPricing?.final_total)"></span></span>
                <button disabled title="Booking flow coming soon"
                    class="bg-[#1e2a4a] text-white px-6 py-2 rounded-lg text-sm font-medium opacity-50 cursor-not-allowed">
                    Book Now
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function searchApp() {
    return {
        today: new Date().toISOString().split('T')[0],
        form: {
            check_in: new Date().toISOString().split('T')[0],
            check_out: (() => { const d = new Date(); d.setDate(d.getDate() + 1); return d.toISOString().split('T')[0]; })(),
            adults: 2,
        },
        loading: false,
        hasSearched: false,
        error: null,
        validationErrors: null,
        results: null,
        selectedRoom: null,
        selectedPlan: null,
        selectedPricing: null,

        get minCheckOut() {
            if (!this.form.check_in) return this.today;
            const d = new Date(this.form.check_in);
            d.setDate(d.getDate() + 1);
            return d.toISOString().split('T')[0];
        },

        incrementAdults() {
            if (this.form.adults < 3) this.form.adults++;
        },

        decrementAdults() {
            if (this.form.adults > 1) this.form.adults--;
        },

        async performSearch() {
            if (!this.form.check_in || !this.form.check_out) {
                this.error = 'Please select check-in and check-out dates.';
                this.validationErrors = null;
                return;
            }

            this.loading = true;
            this.error = null;
            this.validationErrors = null;
            this.selectedRoom = null;
            this.selectedPlan = null;
            this.selectedPricing = null;

            try {
                const response = await fetch('/api/search', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(this.form),
                });

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        this.error = data.message || 'Validation failed';
                        this.validationErrors = data.errors;
                    } else {
                        this.error = data.message || 'Something went wrong. Please try again.';
                    }
                    this.hasSearched = false;
                    return;
                }

                // Add _showBreakdown toggle to each room
                data.data.room_types = data.data.room_types.map(room => ({
                    ...room,
                    _showBreakdown: false,
                }));

                this.results = data.data;
                this.hasSearched = true;
            } catch (e) {
                this.error = 'Network error. Please check your connection and try again.';
                this.hasSearched = false;
            } finally {
                this.loading = false;
            }
        },

        selectRoom(room, plan) {
            this.selectedRoom = room;
            this.selectedPlan = plan;
            this.selectedPricing = room.pricing[plan];
        },

        formatCurrency(value) {
            if (value === null || value === undefined) return '0';
            return Number(value).toLocaleString('en-IN', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2,
            });
        },
    };
}
</script>
@endsection
