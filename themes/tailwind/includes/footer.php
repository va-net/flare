</main>
</div>
</div>
<div x-cloak class="fixed top-0 right-0 z-20 max-w-sm m-8 md:w-full" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-50" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" x-show="flashSuccess">
    <div class="flex items-center gap-2 p-3 bg-white border rounded">
        <div class="flex items-center flex-none text-green-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <p class="flex-1" x-text="flashSuccess"></p>
        <div class="flex items-center flex-none text-gray-400 group group-hover:text-gray-800" @click="flashSuccess = null">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>
    </div>
</div>
<div x-cloak class="fixed top-0 right-0 z-20 max-w-sm m-8 md:w-full" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-50" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" x-show="flashError">
    <div class="flex items-center gap-2 p-3 bg-white border rounded">
        <div class="flex items-center flex-none text-red-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <p class="flex-1" x-text="flashError"></p>
        <div class="flex items-center flex-none text-gray-400 group group-hover:text-gray-800" @click="flashError = null">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>
    </div>
</div>
<script src="https://unpkg.com/alpinejs@3.0.1/dist/cdn.min.js" defer></script>
</body>

</html>