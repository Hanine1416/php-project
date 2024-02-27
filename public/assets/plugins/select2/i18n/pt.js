!function () {
    if($.fn.select2){
        $.fn.select2.defaults.defaults.language  =  {
            errorLoading: function () {
                return "Os resultados não puderam ser carregados."
            }, inputTooLong: function (e) {
                var r = e.input.length - e.maximum, n = "Por favor apague " + r + " ";
                return n += 1 != r ? "caracteres" : "caractere"
            }, inputTooShort: function (e) {
                return "Introduza " + (e.minimum - e.input.length) + " ou mais caracteres"
            }, loadingMore: function () {
                return "A carregar mais resultados…"
            }, maximumSelected: function (e) {
                var r = "Apenas pode seleccionar " + e.maximum + " ";
                return r += 1 != e.maximum ? "itens" : "item"
            }, noResults: function () {
                return "Sem resultados"
            }, searching: function () {
                return "A procurar…"
            }, removeAllItems: function () {
                return "Remover todos os itens"
            }
        }
    }
}();