<?php include_once 'header.php'; ?>

<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=Inter:wght@300;400;600&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">

<style>
    .font-serif-premium { font-family: 'Playfair Display', serif; }
    .font-technical { font-family: 'IBM Plex Mono', monospace; }
    body { font-family: 'Inter', sans-serif; }
</style>

<main class="relative min-h-[calc(100vh-64px)] flex items-center justify-center bg-[#ffffff] overflow-hidden">
    
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f8fafc_1px,transparent_1px),linear-gradient(to_bottom,#f8fafc_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>
    </div>

    <div class="relative z-10 w-full max-w-4xl px-8 py-16">
        <div class="flex flex-col items-center text-center">
            
            <div class="inline-flex items-center gap-3 px-4 py-1.5 mb-10 border border-slate-200 bg-white rounded-sm shadow-sm">
                <span class="relative flex h-2 w-2">
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-slate-400"></span>
                </span>
                <span class="font-technical text-[10px] font-medium tracking-[0.1em] text-slate-500 uppercase">
                    ajeboXTech
                </span>
            </div>

            <h1 class="text-5xl md:text-7xl font-semibold tracking-tight text-slate-900 mb-8 leading-[1.1]">
                Operational <span class="font-serif-premium italic font-normal text-slate-500">Excellence</span> <br/>
                Defined by Data.
            </h1>

            <p class="text-lg text-slate-500 max-w-xl leading-relaxed font-light mb-12">
                Providing an integrated platform for managing inventory, sales, finance, purchasing, and customer operations. Designed to help small businesses streamline processes, improve productivity, and make data-driven decisions.
            </p>

            <div class="flex flex-col sm:flex-row items-center gap-5 w-full sm:w-auto">
                <a href="login.php" class="w-full sm:w-64 px-10 py-4 bg-slate-900 text-white text-xs font-technical uppercase tracking-widest rounded-sm hover:bg-slate-800 transition-all shadow-lg shadow-slate-200">
                    Authenticate
                </a>
                
                <a href="register.php" class="w-full sm:w-64 px-10 py-4 bg-transparent text-slate-600 border border-slate-300 text-xs font-technical uppercase tracking-widest rounded-sm hover:bg-slate-50 hover:border-slate-900 hover:text-slate-900 transition-all text-center">
                    Request Access
                </a>
            </div>

            <div class="mt-24 pt-10 border-t border-slate-100 w-full">
                <p class="font-technical text-[9px] uppercase tracking-[0.3em] text-slate-300">
                    Institutional Logistics Framework &copy; MMXXV
                </p>
            </div>
        </div>
    </div>
</main>

<?php include_once 'footer.php'; ?>