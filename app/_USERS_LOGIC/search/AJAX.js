(function(){
    let currentPage = 1;
    let loading = false;
    let endReached = false;

    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function(){
                func.apply(context, args);
            }, wait);
        };
    }

    function loadResults(page, append = false) {
        const search = document.getElementById('search-input').value;
        const start_date = document.getElementById('start_date').value;
        const end_date = document.getElementById('end_date').value;
        const params = new URLSearchParams({
            search,
            start_date,
            end_date,
            page,
            ajax: 1
        });
        document.getElementById('loading').style.display = 'block';


        setTimeout(() => {
            fetch(`?${params.toString()}`)
                .then(response => response.text())
                .then(html => {
                    if (append) {
                        if(html.trim() === "") {
                            endReached = true;
                        }
                        document.getElementById('results').insertAdjacentHTML('beforeend', html);
                    } else {
                        document.getElementById('results').innerHTML = html;
                        currentPage = 1;
                        endReached = false;
                    }
                    document.getElementById('loading').style.display = 'none';
                    loading = false;
                })
                .catch(() => {
                    document.getElementById('loading').style.display = 'none';
                    loading = false;
                });
        }, 1000);
    }

    window.addEventListener('scroll', function() {
        if (endReached || loading) return;
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 100) {
            loading = true;
            currentPage++;
            loadResults(currentPage, true);
        }
    });

    const debouncedLoad = debounce(function(){ loadResults(1); }, 300);
    document.getElementById('search-input').addEventListener('keyup', debouncedLoad);
    document.getElementById('start_date').addEventListener('change', debouncedLoad);
    document.getElementById('end_date').addEventListener('change', debouncedLoad);

    document.getElementById('advanced-search-btn').addEventListener('click', function() {
        var form = document.getElementById('advanced-search-form');
        if (form.style.maxHeight === '0px' || form.style.maxHeight === '') {
            form.style.display = 'block';
            setTimeout(function() {
                form.style.maxHeight = form.scrollHeight + "px";
            }, 10);
        } else {
            form.style.maxHeight = '0px';
            setTimeout(function() {
                form.style.display = 'none';
            }, 300);
        }
    });
})();
