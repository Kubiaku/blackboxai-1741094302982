<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalesHub - Modern Sales Management</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: '#0061F2',
                        secondary: '#6B7280',
                        success: '#00B74A',
                        warning: '#F59E0B',
                        danger: '#DC2626',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 overflow-x-hidden font-inter">
    <!-- Header -->
    <header class="fixed top-0 right-0 ml-64 w-[calc(100%-16rem)] bg-white shadow-sm z-20 h-16">
        <div class="flex items-center justify-between px-8 h-full">
            <div class="flex items-center space-x-4">
                <button class="lg:hidden text-gray-500 hover:text-gray-700" id="sidebarToggle">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <h2 class="text-2xl font-bold text-gray-800"><?php echo ucfirst($page); ?></h2>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <input type="search" placeholder="Search..." 
                           class="w-64 pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
                <button class="p-2 text-gray-400 hover:text-gray-600 relative">
                    <i class="fas fa-bell text-xl"></i>
                    <span class="absolute top-0 right-0 h-2 w-2 bg-red-500 rounded-full"></span>
                </button>
                <div class="flex items-center space-x-3">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name']); ?>&background=0061F2&color=fff" 
                         alt="Profile" class="w-8 h-8 rounded-full">
                    <div class="flex flex-col">
                        <span class="text-sm font-medium text-gray-700"><?php echo $_SESSION['user_name']; ?></span>
                        <span class="text-xs text-gray-500"><?php echo $_SESSION['user_role']; ?></span>
                    </div>
                </div>
                <form method="post" action="includes/logout.php" class="inline">
                    <button type="submit" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-sign-out-alt text-xl"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>
