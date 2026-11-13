/*
 * "Download PDF" for printable documents (reports, Dental Record).
 *
 * window.downloadDocPdf() renders the page's .doc-sheet onto A4 pages with
 * the printout's 1-inch margins and saves it. The file name comes from
 * <meta name="pdf-filename">. The preview modals call it on the document
 * inside their frame. html2pdf.js (html2canvas + jsPDF) is only fetched
 * the first time it's needed.
 */
(function () {
  function loadLibrary() {
    if (window.html2pdf) return Promise.resolve();
    return new Promise(function (resolve, reject) {
      var s = document.createElement('script');
      s.src = 'https://cdn.jsdelivr.net/npm/html2pdf.js@0.14.0/dist/html2pdf.bundle.min.js';
      s.onload = resolve;
      s.onerror = function () { s.remove(); reject(new Error('Could not load the PDF tool.')); };
      document.head.appendChild(s);
    });
  }

  window.downloadDocPdf = function () {
    var meta = document.querySelector('meta[name="pdf-filename"]');
    var filename = (meta && meta.content) || 'document.pdf';

    return loadLibrary().then(function () {
      return document.fonts ? document.fonts.ready : null;
    }).then(function () {
      return window.html2pdf().set({
        margin: 25.4, // mm = 1 inch, same as @page in print-doc.css
        filename: filename,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
        // Same "keep together" pieces as the print stylesheet.
        pagebreak: {
          mode: ['css', 'legacy'],
          avoid: ['.letterhead', '.doc-title', '.doc-intro', '.doc-keep', '.doc-kv', '.doc-figures', '.doc-teeth', '.doc-legend',
                  '.doc-note', '.doc-section-title', '.doc-section-desc', 'tr', '.doc-signatures'],
        },
      }).from(document.querySelector('.doc-sheet')).save();
    });
  };
})();
