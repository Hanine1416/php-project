!function () {
    if($.fn.select2){
        $.fn.select2.defaults.defaults.language  =  {
            errorLoading: function () {
                return "Die Ergebnisse konnten nicht geladen werden."
            }, inputTooLong: function (e) {
                return "Bitte " + (e.input.length - e.maximum) + " Zeichen weniger eingeben"
            }, inputTooShort: function (e) {
                return "Bitte " + (e.minimum - e.input.length) + " Zeichen mehr eingeben"
            }, loadingMore: function () {
                return "Lade mehr Ergebnisse…"
            }, maximumSelected: function (e) {
                var n = "Sie können nur " + e.maximum + " Eintr";
                return 1 === e.maximum ? n += "ag" : n += "äge", n += " auswählen"
            }, noResults: function () {
                return "Keine Übereinstimmungen gefunden"
            }, searching: function () {
                return "Suche…"
            }, removeAllItems: function () {
                return "Entferne alle Gegenstände"
            }
        }
    }
}();