<x-app-layout>
    <x-slot name="header"></x-slot> <!-- remove header for full-page look -->

    <div class="min-h-screen bg-gray-900 flex items-center justify-center relative overflow-hidden">
        <!-- Background image -->
        <div class="absolute inset-0">
            <img src="{{ asset('images/kevin.jpg') }}" 
                 class="w-full h-full object-cover opacity-60" alt="Background">
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>
        </div>

        <!-- Card -->
        <div class="relative bg-white dark:bg-gray-800 shadow-2xl rounded-xl w-[90%] max-w-5xl overflow-hidden flex flex-col md:flex-row">
            
            <!-- Left side (Login/Register) -->
            <div class="md:w-1/2 p-12 flex flex-col justify-center space-y-8">
                <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Welcome to Knowledge Snippets</h1>
                <p class="text-gray-600 dark:text-gray-300">
                    Save, organize, and share your code snippets easily.
                </p>

                <div class="flex space-x-4">
                    <a href="{{ route('login') }}" 
                       class="flex-1 px-6 py-3 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 text-center shadow">
                        Login
                    </a>
                    <a href="{{ route('register') }}" 
                       class="flex-1 px-6 py-3 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 text-center shadow">
                        Register
                    </a>
                </div>
            </div>

            <!-- Right side (Image / Design) -->
            <div class="md:w-1/2 hidden md:block relative">
                <img src="{{ asset('images/right.jpg') }}" 
                     class="w-full h-full object-cover" alt="Design Image">
                <!-- Optional overlay or design elements -->
                <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-black opacity-30"></div>
            </div>

        </div>
    </div>
</x-app-layout>
