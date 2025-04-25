// Reader.js - PustakaNusa Digital Book Reader
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const readerContainer = document.querySelector('.reader-container');
    const sidebarLeft = document.querySelector('.sidebar-left');
    const sidebarRight = document.querySelector('.sidebar-right');
    const toggleSidebarLeftBtn = document.getElementById('toggle-sidebar-left');
    const toggleSidebarRightBtn = document.getElementById('toggle-sidebar-right');
    const closeSidebarRightBtn = document.getElementById('close-sidebar-right');
    const bookContent = document.getElementById('book-content');
    
    // Font controls
    const fontSizeSlider = document.getElementById('font-size');
    const fontSizeValue = document.getElementById('font-size-value');
    const lineHeightSlider = document.getElementById('line-height');
    const lineHeightValue = document.getElementById('line-height-value');
    
    // Theme buttons
    const themeButtons = document.querySelectorAll('.theme-btn');
    
    // Modal triggers
    const tocBtn = document.getElementById('btn-table-of-contents');
    const bookmarksBtn = document.getElementById('btn-bookmarks');
    const notesBtn = document.getElementById('btn-notes');
    const searchBtn = document.getElementById('btn-search');
    const addBookmarkBtn = document.getElementById('btn-add-bookmark');
    const addNoteBtn = document.getElementById('btn-add-note');
    
    // Initialize Bootstrap modals
    const tocModal = new bootstrap.Modal(document.getElementById('tocModal'));
    const bookmarksModal = new bootstrap.Modal(document.getElementById('bookmarksModal'));
    const notesModal = new bootstrap.Modal(document.getElementById('notesModal'));
    
    // Toggle left sidebar
    if (toggleSidebarLeftBtn) {
        toggleSidebarLeftBtn.addEventListener('click', function() {
            readerContainer.classList.toggle('left-sidebar-collapsed');
        });
    }
    
    // Toggle right sidebar
    if (toggleSidebarRightBtn) {
        toggleSidebarRightBtn.addEventListener('click', function() {
            readerContainer.classList.toggle('right-sidebar-visible');
        });
    }
    
    // Close right sidebar
    if (closeSidebarRightBtn) {
        closeSidebarRightBtn.addEventListener('click', function() {
            readerContainer.classList.remove('right-sidebar-visible');
        });
    }
    
    // Font size adjustment
    if (fontSizeSlider && fontSizeValue && bookContent) {
        fontSizeSlider.addEventListener('input', function() {
            const size = this.value;
            fontSizeValue.textContent = size + 'px';
            bookContent.style.fontSize = size + 'px';
            
            // Save preference to localStorage
            localStorage.setItem('reader_font_size', size);
        });
        
        // Load saved preference
        const savedFontSize = localStorage.getItem('reader_font_size');
        if (savedFontSize) {
            fontSizeSlider.value = savedFontSize;
            fontSizeValue.textContent = savedFontSize + 'px';
            bookContent.style.fontSize = savedFontSize + 'px';
        }
    }
    
    // Line height adjustment
    if (lineHeightSlider && lineHeightValue && bookContent) {
        lineHeightSlider.addEventListener('input', function() {
            const height = this.value;
            lineHeightValue.textContent = height;
            bookContent.style.lineHeight = height;
            
            // Save preference to localStorage
            localStorage.setItem('reader_line_height', height);
        });
        
        // Load saved preference
        const savedLineHeight = localStorage.getItem('reader_line_height');
        if (savedLineHeight) {
            lineHeightSlider.value = savedLineHeight;
            lineHeightValue.textContent = savedLineHeight;
            bookContent.style.lineHeight = savedLineHeight;
        }
    }
    
    // Theme selection
    if (themeButtons && themeButtons.length && bookContent) {
        themeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const theme = this.getAttribute('data-theme');
                
                // Remove active class from all buttons
                themeButtons.forEach(b => b.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Apply theme
                applyTheme(theme);
                
                // Save preference to localStorage
                localStorage.setItem('reader_theme', theme);
            });
        });
        
        // Load saved theme preference
        const savedTheme = localStorage.getItem('reader_theme') || 'light';
        applyTheme(savedTheme);
        
        // Set active button
        themeButtons.forEach(btn => {
            if (btn.getAttribute('data-theme') === savedTheme) {
                btn.classList.add('active');
            }
        });
    }
    
    // Function to apply theme
    function applyTheme(theme) {
        // Remove all theme classes
        bookContent.classList.remove('theme-light', 'theme-sepia', 'theme-dark');
        
        // Add selected theme class
        bookContent.classList.add('theme-' + theme);
        
        // Apply specific styles based on theme
        switch(theme) {
            case 'light':
                bookContent.style.backgroundColor = '#ffffff';
                bookContent.style.color = '#333333';
                break;
            case 'sepia':
                bookContent.style.backgroundColor = '#f4ecd8';
                bookContent.style.color = '#5b4636';
                break;
            case 'dark':
                bookContent.style.backgroundColor = '#2d2d2d';
                bookContent.style.color = '#e0e0e0';
                break;
        }
    }
    
    // Modal triggers
    if (tocBtn) tocBtn.addEventListener('click', () => tocModal.show());
    if (bookmarksBtn) bookmarksBtn.addEventListener('click', () => bookmarksModal.show());
    if (notesBtn) notesBtn.addEventListener('click', () => notesModal.show());
    
    // Bookmark functionality
    if (addBookmarkBtn) {
        addBookmarkBtn.addEventListener('click', function() {
            // Get current page from URL or data attribute
            const urlParams = new URLSearchParams(window.location.search);
            const currentPage = urlParams.get('page') || 1;
            
            // Get stored bookmarks or initialize empty array
            let bookmarks = JSON.parse(localStorage.getItem('reader_bookmarks') || '[]');
            
            // Check if this page is already bookmarked
            const isBookmarked = bookmarks.some(bookmark => bookmark.page === currentPage);
            
            if (!isBookmarked) {
                // Add new bookmark
                bookmarks.push({
                    page: currentPage,
                    title: document.title,
                    timestamp: new Date().toISOString()
                });
                
                // Save to localStorage
                localStorage.setItem('reader_bookmarks', JSON.stringify(bookmarks));
                
                // Show confirmation
                showToast('Halaman berhasil ditandai!');
            } else {
                showToast('Halaman ini sudah ditandai sebelumnya.');
            }
        });
    }
    
    // Notes functionality
    if (addNoteBtn) {
        addNoteBtn.addEventListener('click', function() {
            const selectedText = window.getSelection().toString();
            
            if (selectedText) {
                // Get selected text as context
                const noteContext = selectedText.length > 100 
                    ? selectedText.substring(0, 100) + '...' 
                    : selectedText;
                
                // Prompt for note
                const noteText = prompt('Tambahkan catatan untuk teks yang dipilih:', '');
                
                if (noteText) {
                    // Get current page
                    const urlParams = new URLSearchParams(window.location.search);
                    const currentPage = urlParams.get('page') || 1;
                    
                    // Get stored notes or initialize empty array
                    let notes = JSON.parse(localStorage.getItem('reader_notes') || '[]');
                    
                    // Add new note
                    notes.push({
                        page: currentPage,
                        context: noteContext,
                        note: noteText,
                        timestamp: new Date().toISOString()
                    });
                    
                    // Save to localStorage
                    localStorage.setItem('reader_notes', JSON.stringify(notes));
                    
                    // Show confirmation
                    showToast('Catatan berhasil disimpan!');
                }
            } else {
                showToast('Pilih teks terlebih dahulu untuk menambahkan catatan.');
            }
        });
    }
    
    // Toast notification function
    function showToast(message) {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = 'reader-toast';
        toast.textContent = message;
        
        // Append to reader container
        readerContainer.appendChild(toast);
        
        // Show toast
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        // Hide and remove after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                readerContainer.removeChild(toast);
            }, 300);
        }, 3000);
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Left arrow - previous page
        if (e.key === 'ArrowLeft') {
            const prevBtn = document.querySelector('.reader-bottom-nav a:first-child');
            if (prevBtn && !prevBtn.classList.contains('disabled')) {
                window.location.href = prevBtn.getAttribute('href');
            }
        }
        
        // Right arrow - next page
        if (e.key === 'ArrowRight') {
            const nextBtn = document.querySelector('.reader-bottom-nav a:last-child');
            if (nextBtn && !nextBtn.classList.contains('disabled')) {
                window.location.href = nextBtn.getAttribute('href');
            }
        }
        
        // B key - toggle bookmark
        if (e.key === 'b' && !e.ctrlKey && !e.metaKey) {
            if (addBookmarkBtn) {
                addBookmarkBtn.click();
            }
        }
        
        // Esc key - close sidebar
        if (e.key === 'Escape') {
            readerContainer.classList.remove('right-sidebar-visible');
        }
    });
    
    // Add CSS for toast notifications
    const style = document.createElement('style');
    style.textContent = `
        .reader-toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            z-index: 1000;
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .reader-toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
        
        .theme-light {
            background-color: #ffffff;
            color: #333333;
        }
        
        .theme-sepia {
            background-color: #f4ecd8;
            color: #5b4636;
        }
        
        .theme-dark {
            background-color: #2d2d2d;
            color: #e0e0e0;
        }
    `;
    document.head.appendChild(style);
}); 