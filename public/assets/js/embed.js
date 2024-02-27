/* pinwheel - 2016-01-28 20:04:52 */
if ("object" != typeof Calameo) var Calameo = {};
"undefined" == typeof Calameo.SwfObject && (Calameo.SwfObject = swfobject = function() {
    function f() {
        if (!J) {
            try {
                var Z = j.getElementsByTagName("body")[0].appendChild(C("span"));
                Z.parentNode.removeChild(Z)
            } catch (aa) {
                return
            }
            J = !0;
            for (var X = U.length, Y = 0; X > Y; Y++) U[Y]()
        }
    }

    function K(X) {
        J ? X() : U[U.length] = X
    }

    function s(Y) {
        if (typeof O.addEventListener != D) O.addEventListener("load", Y, !1);
        else if (typeof j.addEventListener != D) j.addEventListener("load", Y, !1);
        else if (typeof O.attachEvent != D) i(O, "onload", Y);
        else if ("function" == typeof O.onload) {
            var X = O.onload;
            O.onload = function() {
                X(), Y()
            }
        } else O.onload = Y
    }

    function z(aa) {
        var X = null,
            Y = c(aa);
        if (Y && "OBJECT" == Y.nodeName)
            if (typeof Y.SetVariable != D) X = Y;
            else {
                var Z = Y.getElementsByTagName(r)[0];
                Z && (X = Z)
            }
        return X
    }

    function A() {
        return !a && F("6.0.65") && (M.win || M.mac) && !(M.wk && M.wk < 312)
    }

    function P(aa, ab, X, Z) {
        a = !0, E = Z || null, B = {
            success: !1,
            id: X
        };
        var ae = c(X);
        if (ae) {
            "OBJECT" == ae.nodeName ? (l = g(ae), Q = null) : (l = ae, Q = X), aa.id = R, (typeof aa.width == D || !/%$/.test(aa.width) && parseInt(aa.width, 10) < 310) && (aa.width = "310"), (typeof aa.height == D || !/%$/.test(aa.height) && parseInt(aa.height, 10) < 137) && (aa.height = "137"), j.title = j.title.slice(0, 47) + " - Flash Player Installation";
            var ad = M.ie && M.win ? "ActiveX" : "PlugIn",
                ac = "MMredirectURL=" + O.location.toString().replace(/&/g, "%26") + "&MMplayerType=" + ad + "&MMdoctitle=" + j.title;
            if (typeof ab.flashvars != D ? ab.flashvars += "&" + ac : ab.flashvars = ac, M.ie && M.win && 4 != ae.readyState) {
                var Y = C("div");
                X += "Calameo.SwfObjectNew", Y.setAttribute("id", X), ae.parentNode.insertBefore(Y, ae), ae.style.display = "none",
                    function() {
                        4 == ae.readyState ? ae.parentNode.removeChild(ae) : setTimeout(arguments.callee, 10)
                    }()
            }
            u(aa, ab, X)
        }
    }

    function g(ab) {
        var aa = C("div");
        if (M.win && M.ie) aa.innerHTML = ab.innerHTML;
        else {
            var Y = ab.getElementsByTagName(r)[0];
            if (Y) {
                var ad = Y.childNodes;
                if (ad)
                    for (var X = ad.length, Z = 0; X > Z; Z++) 1 == ad[Z].nodeType && "PARAM" == ad[Z].nodeName || 8 == ad[Z].nodeType || aa.appendChild(ad[Z].cloneNode(!0))
            }
        }
        return aa
    }

    function u(ai, ag, Y) {
        var X, aa = c(Y);
        if (M.wk && M.wk < 312) return X;
        if (aa)
            if (typeof ai.id == D && (ai.id = Y), M.ie && M.win) {
                var ah = "";
                for (var ae in ai) ai[ae] != Object.prototype[ae] && ("data" == ae.toLowerCase() ? ag.movie = ai[ae] : "styleclass" == ae.toLowerCase() ? ah += ' class="' + ai[ae] + '"' : "classid" != ae.toLowerCase() && (ah += " " + ae + '="' + ai[ae] + '"'));
                var af = "";
                for (var ad in ag) ag[ad] != Object.prototype[ad] && (af += '<param name="' + ad + '" value="' + ag[ad] + '" />');
                aa.outerHTML = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"' + ah + ">" + af + "</object>", N[N.length] = ai.id, X = c(ai.id)
            } else {
                var Z = C(r);
                Z.setAttribute("type", q);
                for (var ac in ai) ai[ac] != Object.prototype[ac] && ("styleclass" == ac.toLowerCase() ? Z.setAttribute("class", ai[ac]) : "classid" != ac.toLowerCase() && Z.setAttribute(ac, ai[ac]));
                for (var ab in ag) ag[ab] != Object.prototype[ab] && "movie" != ab.toLowerCase() && e(Z, ab, ag[ab]);
                aa.parentNode.replaceChild(Z, aa), X = Z
            }
        return X
    }

    function e(Z, X, Y) {
        var aa = C("param");
        aa.setAttribute("name", X), aa.setAttribute("value", Y), Z.appendChild(aa)
    }

    function y(Y) {
        var X = c(Y);
        X && "OBJECT" == X.nodeName && (M.ie && M.win ? (X.style.display = "none", function() {
            4 == X.readyState ? b(Y) : setTimeout(arguments.callee, 10)
        }()) : X.parentNode.removeChild(X))
    }

    function b(Z) {
        var Y = c(Z);
        if (Y) {
            for (var X in Y) "function" == typeof Y[X] && (Y[X] = null);
            Y.parentNode.removeChild(Y)
        }
    }

    function c(Z) {
        var X = null;
        try {
            X = j.getElementById(Z)
        } catch (Y) {}
        return X
    }

    function C(X) {
        return j.createElement(X)
    }

    function i(Z, X, Y) {
        Z.attachEvent(X, Y), I[I.length] = [Z, X, Y]
    }

    function F(Z) {
        var Y = M.pv,
            X = Z.split(".");
        return X[0] = parseInt(X[0], 10), X[1] = parseInt(X[1], 10) || 0, X[2] = parseInt(X[2], 10) || 0, Y[0] > X[0] || Y[0] == X[0] && Y[1] > X[1] || Y[0] == X[0] && Y[1] == X[1] && Y[2] >= X[2] ? !0 : !1
    }

    function v(ac, Y, ad, ab) {
        if (!M.ie || !M.mac) {
            var aa = j.getElementsByTagName("head")[0];
            if (aa) {
                var X = ad && "string" == typeof ad ? ad : "screen";
                if (ab && (n = null, G = null), !n || G != X) {
                    var Z = C("style");
                    Z.setAttribute("type", "text/css"), Z.setAttribute("media", X), n = aa.appendChild(Z), M.ie && M.win && typeof j.styleSheets != D && j.styleSheets.length > 0 && (n = j.styleSheets[j.styleSheets.length - 1]), G = X
                }
                M.ie && M.win ? n && typeof n.addRule == r && n.addRule(ac, Y) : n && typeof j.createTextNode != D && n.appendChild(j.createTextNode(ac + " {" + Y + "}"))
            }
        }
    }

    function w(Z, X) {
        if (m) {
            var Y = X ? "visible" : "hidden";
            J && c(Z) ? c(Z).style.visibility = Y : v("#" + Z, "visibility:" + Y)
        }
    }

    function L(Y) {
        var Z = /[\\\"<>\.;]/,
            X = null != Z.exec(Y);
        return X && typeof encodeURIComponent != D ? encodeURIComponent(Y) : Y
    }
    var l, Q, E, B, n, G, D = "undefined",
        r = "object",
        S = "Shockwave Flash",
        W = "ShockwaveFlash.ShockwaveFlash",
        q = "application/x-shockwave-flash",
        R = "Calameo.SwfObjectExprInst",
        x = "onreadystatechange",
        O = window,
        j = document,
        t = navigator,
        T = !1,
        U = [],
        o = [],
        N = [],
        I = [],
        J = !1,
        a = !1,
        m = !0,
        M = function() {
            var aa = typeof j.getElementById != D && typeof j.getElementsByTagName != D && typeof j.createElement != D,
                ah = t.userAgent.toLowerCase(),
                Y = t.platform.toLowerCase(),
                ae = Y ? /win/.test(Y) : /win/.test(ah),
                ac = Y ? /mac/.test(Y) : /mac/.test(ah),
                af = /webkit/.test(ah) ? parseFloat(ah.replace(/^.*webkit\/(\d+(\.\d+)?).*$/, "$1")) : !1,
                X = !1,
                ag = [0, 0, 0],
                ab = null;
            if (typeof t.plugins != D && typeof t.plugins[S] == r) ab = t.plugins[S].description, !ab || typeof t.mimeTypes != D && t.mimeTypes[q] && !t.mimeTypes[q].enabledPlugin || (T = !0, X = !1, ab = ab.replace(/^.*\s+(\S+\s+\S+$)/, "$1"), ag[0] = parseInt(ab.replace(/^(.*)\..*$/, "$1"), 10), ag[1] = parseInt(ab.replace(/^.*\.(.*)\s.*$/, "$1"), 10), ag[2] = /[a-zA-Z]/.test(ab) ? parseInt(ab.replace(/^.*[a-zA-Z]+(.*)$/, "$1"), 10) : 0);
            else if (typeof O.ActiveXObject != D) try {
                var ad = new ActiveXObject(W);
                ad && (ab = ad.GetVariable("$version"), ab && (X = !0, ab = ab.split(" ")[1].split(","), ag = [parseInt(ab[0], 10), parseInt(ab[1], 10), parseInt(ab[2], 10)]))
            } catch (Z) {}
            return {
                w3: aa,
                pv: ag,
                wk: af,
                ie: X,
                win: ae,
                mac: ac
            }
        }();
    (function() {
        M.w3 && ((typeof j.readyState != D && "complete" == j.readyState || typeof j.readyState == D && (j.getElementsByTagName("body")[0] || j.body)) && f(), J || (typeof j.addEventListener != D && j.addEventListener("DOMContentLoaded", f, !1), M.ie && M.win && (j.attachEvent(x, function() {
            "complete" == j.readyState && (j.detachEvent(x, arguments.callee), f())
        }), O == top && ! function() {
            if (!J) {
                try {
                    j.documentElement.doScroll("left")
                } catch (X) {
                    return void setTimeout(arguments.callee, 0)
                }
                f()
            }
        }()), M.wk && ! function() {
            return J ? void 0 : /loaded|complete/.test(j.readyState) ? void f() : void setTimeout(arguments.callee, 0)
        }(), s(f)))
    })(),
    function() {
        M.ie && M.win && window.attachEvent("onunload", function() {
            for (var ac = I.length, ab = 0; ac > ab; ab++) I[ab][0].detachEvent(I[ab][1], I[ab][2]);
            for (var Z = N.length, aa = 0; Z > aa; aa++) y(N[aa]);
            for (var Y in M) M[Y] = null;
            M = null;
            for (var X in Calameo.SwfObject) Calameo.SwfObject[X] = null;
            Calameo.SwfObject = null
        })
    }();
    return {
        registerObject: function(ab, X, aa, Z) {
            if (M.w3 && ab && X) {
                var Y = {};
                Y.id = ab, Y.swfVersion = X, Y.expressInstall = aa, Y.callbackFn = Z, o[o.length] = Y, w(ab, !1)
            } else Z && Z({
                success: !1,
                id: ab
            })
        },
        getObjectById: function(X) {
            return M.w3 ? z(X) : void 0
        },
        embedSWF: function(ab, ah, ae, ag, Y, aa, Z, ad, af, ac) {
            var X = {
                success: !1,
                id: ah
            };
            M.w3 && !(M.wk && M.wk < 312) && ab && ah && ae && ag && Y ? (w(ah, !1), K(function() {
                ae += "", ag += "";
                var aj = {};
                if (af && typeof af === r)
                    for (var al in af) aj[al] = af[al];
                aj.data = ab, aj.width = ae, aj.height = ag;
                var am = {};
                if (ad && typeof ad === r)
                    for (var ak in ad) am[ak] = ad[ak];
                if (Z && typeof Z === r)
                    for (var ai in Z) typeof am.flashvars != D ? am.flashvars += "&" + ai + "=" + Z[ai] : am.flashvars = ai + "=" + Z[ai];
                if (F(Y)) {
                    var an = u(aj, am, ah);
                    aj.id == ah && w(ah, !0), X.success = !0, X.ref = an
                } else {
                    if (aa && A()) return aj.data = aa, void P(aj, am, ah, ac);
                    w(ah, !0)
                }
                ac && ac(X)
            })) : ac && ac(X)
        },
        switchOffAutoHideShow: function() {
            m = !1
        },
        ua: M,
        getFlashPlayerVersion: function() {
            return {
                major: M.pv[0],
                minor: M.pv[1],
                release: M.pv[2]
            }
        },
        hasFlashPlayerVersion: F,
        createSWF: function(Z, Y, X) {
            return M.w3 ? u(Z, Y, X) : void 0
        },
        showExpressInstall: function(Z, aa, X, Y) {
            M.w3 && A() && P(Z, aa, X, Y)
        },
        removeSWF: function(X) {
            M.w3 && y(X)
        },
        createCSS: function(aa, Z, Y, X) {
            M.w3 && v(aa, Z, Y, X)
        },
        addDomLoadEvent: K,
        addLoadEvent: s,
        getQueryParamValue: function(aa) {
            var Z = j.location.search || j.location.hash;
            if (Z) {
                if (/\?/.test(Z) && (Z = Z.split("?")[1]), null == aa) return L(Z);
                for (var Y = Z.split("&"), X = 0; X < Y.length; X++)
                    if (Y[X].substring(0, Y[X].indexOf("=")) == aa) return L(Y[X].substring(Y[X].indexOf("=") + 1))
            }
            return ""
        },
        expressInstallCallback: function() {
            if (a) {
                var X = c(R);
                X && l && (X.parentNode.replaceChild(l, X), Q && (w(Q, !0), M.ie && M.win && (l.style.display = "block")), E && E(B)), a = !1
            }
        }
    }
}()), "undefined" == typeof Calameo.SwfMacMouseWheel && (Calameo.SwfMacMouseWheel = function() {
    if (!Calameo.SwfObject) return null;
    var u = navigator.userAgent.toLowerCase(),
        p = navigator.platform.toLowerCase(),
        d = p ? /mac/.test(p) : /mac/.test(u);
    if (!d) return null;
    var k = [],
        r = function(event) {
            var o = 0;
            return event.wheelDelta ? (o = event.wheelDelta / 120, window.opera && (o = -o)) : event.detail && (o = -event.detail), event.preventDefault && event.preventDefault(), o
        },
        l = function(event) {
            for (var c, o = r(event), i = 0; i < k.length; i++) c = Calameo.SwfObject.getObjectById(k[i]), "function" == typeof c.externalMouseEvent && c.externalMouseEvent(o)
        };
    return window.addEventListener && window.addEventListener("DOMMouseScroll", l, !1), window.onmousewheel = document.onmousewheel = l, {
        registerObject: function(m) {
            k[k.length] = m
        }
    }
}), "undefined" == typeof Calameo.Ajax && (Calameo.Ajax = {
    getHTTPObject: function() {
        var A = !1;
        if ("undefined" != typeof ActiveXObject) try {
                A = new ActiveXObject("Msxml2.XMLHTTP")
            } catch (C) {
                try {
                    A = new ActiveXObject("Microsoft.XMLHTTP")
                } catch (B) {
                    A = !1
                }
            } else if (window.XMLHttpRequest) try {
                A = new XMLHttpRequest
            } catch (C) {
                A = !1
            }
            return A
    },
    load: function(url, callback, error, format) {
        var http = this.init();
        if (http && url) {
            if (http.overrideMimeType && http.overrideMimeType("text/xml"), !format) var format = "text";
            format = format.toLowerCase(), http.open("GET", url, !0), http.onreadystatechange = function() {
                if (4 == http.readyState)
                    if (200 == http.status) {
                        var result = "";
                        const contentType = http.getResponseHeader('Content-Type');
                        let res = http.responseText;
                        if (contentType.includes('application/json')) {
                            res = JSON.parse(res);
                        }
                        http.responseText && (result = http.responseText),
                        "j" == format.charAt(0) && (result = result.replace(/[\n\r]/g, ""),
                            result = "(" + res + ")"), callback && callback(result)
                    } else error && error(http.status)
            }, http.send(null)
        }
    },
    init: function() {
        return this.getHTTPObject()
    }
}), "object" != typeof Calameo.EmbedConfig && (Calameo.EmbedConfig = {
    LangID: null,
    Language: null,
    Locales: null,
    BgColor: "FFFFFF",
    PageShadow: !0,
    PageFx: !0,
    Button: !0,
    ButtonColor: "FFFFFF",
    ButtonBgColor: "000000",
    ButtonLabel: "",
    ForceFlashPlayerVersion: !0,
    ViewerDomain: "",
    LoaderVersion: "",
    ViewerVersion: "3",
    ViewerConfig: {},
    IsSelfhosted: !1,
    WMode: "opaque",
    ExpressInstall: "http://s1.calameoassets.com/calameo-v4/expressInstall.swf",
    ForceViewer: "",
    UseIFrame: !1,
    IFrameVersion: "",
    IFrameDomain: "//v.calameo.com/",
    MobileDirect: 0,
    MobileEmbedVersion: 2,
    MobileWebserviceUrl: "//d.calameo.com/3.0.0/book.php",
    MobileDomain: "js/",
    MobileLibVersion: "1.1.3",
    MobileCssVersion: "1.1.6-min",
    MobileViewerVersion: "1.1.6-min",
    AdContainer: "ac",
    FlyerContainer: "flyer"
}), "object" != typeof Calameo.EmbeddedViewer && (Calameo.EmbeddedViewer = function(config) {
    if ("undefined" == typeof console && (console = {
            log: function(msg) {}
        }), this.Version = "1.2.0", this.FlashPlayerVersion = {}, this.ViewerID = "cviewer", this.Locales = {}, this.BookData = null, this.UrlParams = {}, this.AdPosition = null, this.AdContainer = null, this.FlyerContainer = null, this.launch = function() {
            if (this.handleAd(), this.configure(), Calameo.EmbedConfig.WMode = "transparent" == Calameo.EmbedConfig.WMode.toLowerCase() ? "transparent" : "opaque", this.localize(), "string" != typeof Calameo.EmbedConfig.ViewerConfig.bkcode || "" == Calameo.EmbedConfig.ViewerConfig.bkcode) return this.error("Unknown publication");
            if (Calameo.EmbedConfig.ViewerConfig.page && (Calameo.EmbedConfig.ViewerConfig.page = parseInt(Calameo.EmbedConfig.ViewerConfig.page)), Calameo.EmbedConfig.ForceFlashPlayerVersion === !1) this.FlashPlayerVersion = Calameo.SwfObject.getFlashPlayerVersion(), 10 == this.FlashPlayerVersion.major && this.FlashPlayerVersion.minor >= 2 && (this.FlashPlayerVersion.major = 10.2);
            else {/*
                var fpv = Calameo.EmbedConfig.ForceFlashPlayerVersion.split(".");
                this.FlashPlayerVersion = {
                    major: parseInt(fpv[0]),
                    minor: fpv[1] ? parseInt(fpv[1]) : 0,
                    build: 0
                }*/
            }
            return "mobile" == Calameo.EmbedConfig.ForceViewer ? this.mobile() : "desktop" == Calameo.EmbedConfig.ForceViewer ? void(this.FlashPlayerVersion.major < 6 ? this.install() : this.embed()) : void(Calameo.Platform.isSafari() && this.FlashPlayerVersion.major < 9 ? this.mobile() : Calameo.Platform.isChrome() && this.FlashPlayerVersion.major < 9 ? this.mobile() : Calameo.Platform.isAndroid() ? this.mobile() : Calameo.Platform.isIOS() ? this.mobile() : (this.showAd(), this.FlashPlayerVersion.major < 9 ? this.install() : this.embed()))
        }, this.configure = function() {
            var url = new String(document.location.href),
                query = url.split("?");
            if (this.UrlParams = {}, "undefined" != typeof query[1]) {
                query[1] = query[1].replace(/&amp;/gi, "&");
                for (var pieces = query[1].split("&"), a = 0; a < pieces.length; a++) {
                    var p = pieces[a].split("=");
                    "" != p[0] && "viewerconfig" != p[0] && (this.UrlParams[p[0]] = p[1])
                }
                for (a in this.UrlParams) {
                    var test = !1;
                    for (i in Calameo.EmbedConfig)
                        if (a.toLowerCase() == i.toLowerCase()) {
                            test = !0, Calameo.EmbedConfig[i] = this.UrlParams[a];
                            break
                        }
                    test || (Calameo.EmbedConfig.ViewerConfig[a] = this.UrlParams[a])
                }
            }
            Calameo.EmbedConfig.ViewerConfig.mode = "undefined" == typeof Calameo.EmbedConfig.ViewerConfig.mode ? "" : Calameo.EmbedConfig.ViewerConfig.mode
        }, this.localize = function() {
            var langid = Calameo.EmbedConfig.LangID || Calameo.EmbedConfig.Language;
            if ("undefined" == typeof Calameo.EmbeddedLocales[langid]) {
                var langcodes = {
                        fr: "fr",
                        en: "en",
                        es: "es",
                        de: "de",
                        it: "it",
                        pt: "pt",
                        ru: "ru",
                        ko: "kr",
                        zh: "cn",
                        ja: "jp"
                    },
                    langCode = navigator.language || navigator.userLanguage || navigator.browserLanguage || navigator.systemLanguage,
                    lang = langCode.toLowerCase();
                lang = lang.substr(0, 2), "undefined" != typeof langcodes[lang] && (langid = langcodes[lang])
            }
            "undefined" == typeof Calameo.EmbeddedLocales[langid] && (langid = "en"), this.Locales = Calameo.EmbeddedLocales[langid], Calameo.EmbedConfig.Language = Calameo.EmbedConfig.ViewerConfig.langid = Calameo.EmbedConfig.ViewerConfig.language = langid
        }, this.embed = function() {
            if (Calameo.Fullscreen.hasFullscreen() && Calameo.Fullscreen.build(function(isFullscreen) {
                    var swf = document.getElementById("cviewer");
                    swf && swf.onBrowserFullscreenHandler && swf.onBrowserFullscreenHandler(isFullscreen)
                }), Calameo.EmbedConfig.ViewerVersion.indexOf("2.") < 0 && (parseInt(Calameo.EmbedConfig.UseIFrame) > 0 || Calameo.EmbedConfig.UseIFrame === !0 || "true" == Calameo.EmbedConfig.UseIFrame)) {
                var ifr = document.createElement("iframe"),
                    url = Calameo.EmbedConfig.IFrameDomain + "index" + Calameo.EmbedConfig.IFrameVersion + ".htm",
                    params = {};
                for (var s in Calameo.EmbedConfig.ViewerConfig) "" != Calameo.EmbedConfig.ViewerConfig[s] && (params[s] = encodeURIComponent(Calameo.EmbedConfig.ViewerConfig[s]));
                for (s in this.UrlParams) params[s] = encodeURIComponent(this.UrlParams[s]);
                var i = 0;
                for (s in params) url += (0 == i ? "?" : "&") + s + "=" + params[s], i++;
                ifr.src = url, ifr.frameBorder = "0";
                var c = document.getElementById("vc");
                return c.replaceChild(ifr, c.firstChild), ifr.setAttribute("id", "ifr"), ifr.setAttribute("width", "100%"), ifr.setAttribute("height", "100%"), ifr.setAttribute("frameBorder", "0"), ifr.setAttribute("border", "0"), ifr.setAttribute("scrolling", "no"), ifr.setAttribute("seamless", "seamless"), ifr.setAttribute("allowTransparency", "true"), void ifr.setAttribute("allowfullscreen", "true")
            }
            var params = {
                    scale: "noscale",
                    loop: "false",
                    salign: "t",
                    quality: "high",
                    bgcolor: "" != Calameo.EmbedConfig.BgColor ? "#" + Calameo.EmbedConfig.BgColor : "",
                    seamlesstabbing: "true",
                    allowscriptaccess: "samedomain",
                    allowfullscreen: "true",
                    wmode: Calameo.EmbedConfig.WMode
                },
                attributes = {
                    id: "cviewer",
                    name: "cviewer"
                };
            if (this.setExternalInterfaceHandlers(), window.onMouseWheelHandler = function(event) {
                    event || (event = window.event), event.preventDefault ? event.preventDefault() : event.returnValue = !1, event.stopPropagation ? event.stopPropagation() : event.cancelBubble = !0;
                    var swf = window.document[attributes.id];
                    if (swf && "undefined" != typeof swf.handleMouseWheel) {
                        var delta = 0;
                        event.wheelDelta ? (delta = event.wheelDelta / 40, Calameo.Platform.isOpera() || Calameo.Platform.isSafari() ? (delta /= 3, Calameo.Platform.isSafari() && Calameo.Platform.isMacOS() && (delta = -delta), delta = Math.max(-1, Math.min(1, delta))) : Calameo.Platform.isChrome() && Calameo.Platform.isMacOS() && (delta = 40 * -delta)) : event.detail && (delta = Calameo.Platform.isMacOS() ? event.detail : -event.detail), delta = Math.max(-3, Math.min(3, delta));
                        var o = {
                            localX: event.pageX,
                            localY: event.pageY,
                            delta: delta,
                            ctrlKey: event.ctrlKey,
                            altKey: event.altKey,
                            shiftKey: event.shiftKey
                        };
                        swf.handleMouseWheel(o)
                    }
                }, Calameo.Platform.isMozilla() || Calameo.Platform.isChrome()) {
                var evt = Calameo.Platform.isChrome() ? "mousewheel" : "DOMMouseScroll";
                window.addEventListener(evt, window.onMouseWheelHandler, !1)
            } else window.onmousewheel = document.onmousewheel = window.onMouseWheelHandler;
            var urls = {
                    alt: "",
                    expressInstall: Calameo.EmbedConfig.ExpressInstall
                },
                swfUrl = "";
            if (0 === Calameo.EmbedConfig.ViewerVersion.indexOf("2.")) {
                params.allowscriptaccess = "always";
                var viewers = {
                    viewer: Calameo.EmbedConfig.ViewerDomain + escape(Calameo.EmbedConfig.ViewerVersion) + "/cviewer.swf",
                    mini: Calameo.EmbedConfig.ViewerDomain + escape(Calameo.EmbedConfig.ViewerVersion) + "/cmini.swf"
                };
                swfUrl = "mini" == Calameo.EmbedConfig.ViewerConfig.mode ? viewers.mini : viewers.viewer
            } else {
                for (var sv, pos, viewers = {
                        10.2: Calameo.EmbedConfig.ViewerDomain + escape(Calameo.EmbedConfig.ViewerVersion) + "/cloader-fp10.2" + Calameo.EmbedConfig.LoaderVersion + ".swf",
                        10: Calameo.EmbedConfig.ViewerDomain + escape(Calameo.EmbedConfig.ViewerVersion) + "/cloader-fp10" + Calameo.EmbedConfig.LoaderVersion + ".swf",
                        9: Calameo.EmbedConfig.ViewerDomain + escape(Calameo.EmbedConfig.ViewerVersion) + "/cloader-fp9" + Calameo.EmbedConfig.LoaderVersion + ".swf",
                        "default": Calameo.EmbedConfig.ViewerDomain + escape(Calameo.EmbedConfig.ViewerVersion) + "/cloader-fp9" + Calameo.EmbedConfig.LoaderVersion + ".swf"
                    }, v = parseFloat(this.FlashPlayerVersion.major + "." + this.FlashPlayerVersion.minor); v > 6;) {
                    if (sv = String(v), pos = sv.indexOf("."), pos >= 0 && (sv = sv.substr(0, pos + 2), sv = sv.replace(/\.0+/i, "")), "undefined" != typeof viewers[sv]) {
                        swfUrl = viewers[v];
                        break
                    }
                    v = (10 * v - 1) / 10
                }
                "undefined" == typeof swfUrl && (swfUrl = viewers["default"])
            }
            var fv = Calameo.EmbedConfig.ViewerConfig;
            fv.wmode = Calameo.EmbedConfig.WMode, Calameo.SwfObject.embedSWF(swfUrl, "vci", "100%", "100%", "9.0.0", urls.expressInstall, fv, params, attributes, Calameo.SwfMacMouseWheel)
        }, this.placeholder = function() {
            var ph = document.getElementById("ph");
            return ph.className = "mini" == Calameo.EmbedConfig.ViewerConfig.mode ? "mini" : "viewer", ph
        }, this.error = function(msg) {
            "undefined" == typeof msg && (msg = this.Locales.error), this.stat(Calameo.EmbedConfig.ViewerConfig.mode, "error");
            var div = document.getElementById("ph-content");
            div.innerHTML = msg, div.style.display = "block";
            var ph = this.placeholder();
            Calameo.Effects.fadeIn(ph, 500), ph.style.display = "block"
        }, this.mobile = function() {
            if (Calameo.EmbedConfig.IsSelfhosted) return void(this.FlashPlayerVersion.major < 6 ? this.install() : this.embed());
            this.CssUrl = Calameo.EmbedConfig.MobileDomain + "common-" + escape(Calameo.EmbedConfig.MobileCssVersion) + ".css", this.LibUrl = Calameo.EmbedConfig.MobileDomain + "libs-" + escape(Calameo.EmbedConfig.MobileLibVersion) + ".js", this.GblUrl = Calameo.EmbedConfig.MobileDomain + "cviewer-" + escape(Calameo.EmbedConfig.MobileViewerVersion) + ".js";
            var that = this;
            if (parseInt(Calameo.EmbedConfig.MobileDirect) > 0 || Calameo.EmbedConfig.MobileDirect === !0 || "true" == Calameo.EmbedConfig.MobileDirect) return window.Config = Calameo.EmbedConfig.ViewerConfig, document.getElementsByTagName("body")[0].id = "", this.loadCss(Calameo.EmbedConfig.IFrameDomain + this.CssUrl), this.loadScript(Calameo.EmbedConfig.IFrameDomain + this.LibUrl, "utf-8"), that.isFrameworkLoaded = !1, that.isViewerLoaded = !1, void(that.frameworkLoaderInterval = setInterval(function() {
                if ("undefined" != typeof Ext)
                    if (that.isFrameworkLoaded) {
                        if ("0.9.9-min" === Calameo.EmbedConfig.MobileViewerVersion && !that.isViewerLoaded && "undefined" != typeof Calameo.Viewer) {
                            that.isViewerLoaded = !0, clearInterval(that.frameworkLoaderInterval);
                            var conf = {};
                            conf.Config = Calameo.EmbedConfig.ViewerConfig, conf.Config.BgColor = "", conf.BookVar = Calameo.EmbedConfig.ViewerConfig, Ext.setup({
                                statusBarStyle: "black",
                                onReady: function() {
                                    window.CalameoViewer = new Calameo.Viewer(conf)
                                }
                            })
                        }
                    } else that.isFrameworkLoaded = !0, that.frameworkLoaderInterval = void 0, Calameo.EmbedConfig.ViewerVersion.indexOf("2.") < 0 && clearInterval(that.frameworkLoaderInterval), that.loadScript(Calameo.EmbedConfig.IFrameDomain + that.GblUrl, "utf-8")
            }, 20));
            var div = document.getElementById("ph-content");
            if (div) {
                var ph = this.placeholder();
                switch (ph.style.display = "none", parseInt(Calameo.EmbedConfig.MobileEmbedVersion)) {
                    case 1:
                        div.className = "mobile", div.style.backgroundColor = "#0956CD", div.style.backgroundImage = "-webkit-gradient(linear,left top,left bottom,from(#50A6F2),to(#0956CD))", div.style.display = "block";
                        var text = document.createElement("div");
                        text.innerHTML = this.Locales.readOnIPad + "<br/><span>(iOS, Android, Safari, Chrome)</span>", text.style.backgroundImage = "url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAjCAYAAACU9ioYAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAABAFJREFUeNrUlm1oW1UYx59z70mTbl2atGmWtmmSNku7SdY27doitkO3KgPBF7aOrhvEgR/FKTJE8IuKyMDB8LvDD9taX4aTIcKQToXBoOhcrR2KcxLYmjTNa5fcm96X47kn7Uzae9NUP/lAyH0593ef//N2D3Y5m8DltMP+kUHr8P4Dz8zfubsTY16FjYboj5RekGSZe3yoLxH567drb719JplIZQEXCiIghPgDo4defWLk4EtPjeJ6IEQPqGcI8yhnt9uDVmvdO5nssgQHnxwOfHTu7LvkP9qVy5Nn3zj18l7008z377f7Hwvb7I5WPRdi0YV78Xh8QfPGZrc53G5PQG9dPB67Nz93+zwOdHY1WmrrdhhpikUjPyTise8Qh1A2Hd9nBKTvq1EJb8GyJKvEQogRsLt3KEz/wtUElOMQYI4aNtVY5mZnrt76ceYrEz2GLZiiyIU2r3+0NzT4NM/zGFMpPEJcTWrp/o27v//88UI0AbRsoDqPOIhGF2Hf4DAZGBp+lteIxcoiwPE86uzcBdu274AHD2JA720Ky2SWQRAlMJstPE00aIHjHq2gJ6qigM/rhjZ3M5WiVoSl0llIJDNAc1V+r/SEsJgo4PG0QGvLTnasB0tnspBKZVgSAIExcM1kWQGvV4O6yqBFmRosy451vTfOnkqhreBm8pWiZ0xmVmtVw3DgyiVB5be1sDj9Ov8HlbpclFkpWZuVhqqq4HI1wcNcvqJnVQO1VGnQamBVArdm/y+gYZDIantWE8ZNPWT9yYoYseRsBUg2wmjDU0hn1x4YG3uRATeDYmPPinxfRwCs9Tboa3KyQp+a+oJBt9R6a7COXV3Q6GhiIEEQYGCgD8Lh448Kvipgca4RaPcHoKGhsWw4CIII3d1BmJg4yq7rQbGeZ+0dGsxBp4684QHN01Coh8EmJz+nM1Qtqw/un4wUYX4m06k7C9dMFEXo7w8V5aNy+XiNTt9EPB4fTUA9SCti+QdyNQGk5MF87iEEg7vhyOHnYSmZZ2/X6hRTkCLLkhDsGRpTVclHHTWVhICYzeZaQcjntMhuq91eVygUBFQyKV44PCHlBKlHFAWBhkjG9BmVvllqcDT30/v96+XN/XL7zjdXPx2nw1U+Mn7yQqiPal1nNrZzWLxPpSucyYQ5xBk31bfXvj4XiURm6W5kfuri+Q8qdpVKv57Xp6f/jC1Eo0aLPF6fr9nlBKvVCl6fP2C0bmWlkEunl9LQu3dP45unXztptKuiC9XZWzcv3Lwx/Uk+n5eN1n145r1XDo2ONKN2TytoSTxx4thzp14/PW5vcNau62stxXWr02hZK4jSAkgmFsUrly99OXnx0mexeBIQMd4n/Sv7W4ABAD55NYP9zhO8AAAAAElFTkSuQmCC)", div.appendChild(text), Calameo.Effects.fadeIn(ph, 500), ph.style.cursor = "pointer", ph.style.display = "block", ph.addEventListener("click", function(e) {
                            that.openBlankMobile()
                        }), this.stat(Calameo.EmbedConfig.Mode, "mobile");
                        break;
                    case 2:
                    default:
                        this.BookData = null;
                        for (var allowedVars = ["apikey", "authid", "bkcode", "expires", "ip", "login", "password", "signature", "subid"], url = Calameo.EmbedConfig.MobileWebserviceUrl, i = 0; i < allowedVars.length; i++) "undefined" != typeof Calameo.EmbedConfig.ViewerConfig[allowedVars[i]] && (url += (url.indexOf("?") < 0 ? "?" : "&") + allowedVars[i] + "=" + Calameo.EmbedConfig.ViewerConfig[allowedVars[i]]);
                        url += "&callback=_jsonBook", window._jsonBook = function(data) {
                            that.onBookDataReady(data)
                        }, Calameo.Ajax.load(url, function(data) {
                            try {
                                eval(data)
                            } catch (e) {
                                that.error()
                            }
                        }, function(status) {
                            that.error()
                        })
                }
            }
        }, this.escapeHTML = function(str) {
            return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;")
        }, this.onBookDataReady = function(data) {
            if ("ok" == data.status) {
                var that = this;
                this.BookData = data.content, document.getElementsByTagName("title")[0].innerHTML = this.escapeHTML(this.BookData.name), this.stat(Calameo.EmbedConfig.Mode, "mobile");
                var ph = this.placeholder();
                ph.style.background = "transparent";
                var div = document.getElementById("ph-content");
                div.className = "mobile-new";
                var holder = document.createElement("div");
                holder.id = "mobile-holder";
                var image = document.createElement("img");
                if (image.id = "mobile-img", image.onload = function() {
                        image.onload = function() {}, that.onMobileReady()
                    }, image.onerror = function() {
                        image.onerror = function() {}, image.src = "http://s1.calameoassets.com/calameo-v4/images/buttons/" + Calameo.EmbedConfig.Language + "/empty_book_picture.png", image.style.opacity = "0.7", that.onMobileReady()
                    }, (Calameo.EmbedConfig.PageShadow === !1 || 0 === parseInt(Calameo.EmbedConfig.PageShadow, 10) || "false" === Calameo.EmbedConfig.PageShadow) && (image.style.boxShadow = "none"), holder.appendChild(image), Calameo.EmbedConfig.PageFx === !0 || 1 === parseInt(Calameo.EmbedConfig.PageFx, 10) || "true" === Calameo.EmbedConfig.PageFx) {
                    var fx = document.createElement("div");
                    fx.id = "mobile-fx", holder.appendChild(fx)
                }
                if (div.appendChild(holder), Calameo.EmbedConfig.Button === !0 || 1 === parseInt(Calameo.EmbedConfig.Button, 10) || "true" === Calameo.EmbedConfig.Button) {
                    var button = document.createElement("div");
                    button.id = "mobile-button", button.innerHTML = this.Locales.readOnIPad, (Calameo.Platform.isIOS() || Calameo.Platform.isAndroid()) && (button.style.fontSize = "21px"), div.appendChild(button)
                }
                if (div.addEventListener("click", function(e) {
                        that.openBlankMobile()
                    }), this.onMobileResizeHandler(), window.addEventListener("resize", function() {
                        that.onMobileResizeHandler()
                    }), "PLATINUM" == this.BookData.account.mode && ("" != Calameo.EmbedConfig.ButtonLabel && (button.innerHTML = Calameo.EmbedConfig.ButtonLabel), button.style.color = "#" + Calameo.EmbedConfig.ButtonColor, button.style.borderColor = "#" + Calameo.EmbedConfig.ButtonColor, button.style.backgroundColor = "#" + Calameo.EmbedConfig.ButtonBgColor), this.BookData.features.branding.enabled) {
                    var a = document.createElement("a");
                    a.id = "mobile-branding", a.href = this.BookData.features.branding.url, a.target = "_blank", document.getElementsByTagName("body")[0].appendChild(a)
                }
            } else this.error(data.content.msg)
        }, this.onMobileResizeHandler = function() {
            var w, h, pr = this.BookData.pages[0].w / this.BookData.pages[0].h,
                wr = window.innerWidth / window.innerHeight;
            pr > wr ? (w = Math.round(window.innerWidth), h = Math.round(w / pr)) : (h = Math.round(window.innerHeight), w = Math.round(h * pr));
            var image = document.getElementById("mobile-img");
            if (image.style.width = w + "px", image.style.height = h + "px", image.src.indexOf("empty_book_picture.png") < 0) {
                var pageUrl;
                pageUrl = 100 >= w ? this.BookData.url.thumbnail : 150 >= w ? this.BookData.url.image : 300 >= w ? this.BookData.url.poster : this.BookData.domains.image.replace("://www.", "://" + this.BookData.pages[0].i.sd + ".") + this.BookData.key + "/" + this.BookData.pages[0].i.u, pageUrl = pageUrl.replace(/^http[s]?:/i, ""), pageUrl != image.src && (image.src = pageUrl)
            }
        }, this.onMobileReady = function() {
            var ph = this.placeholder();
            ph.style.display = "block"
        }, this.openBlankMobile = function() {
            if (this.BookData && "FREE" == this.BookData.account.mode) {
                var url = this.BookData.url.view;
                return url += (url.indexOf("?") > 0 ? "&" : "?") + "trackersource=embed", void(window.top.location.href = url)
            }
            var width = screen.width - 50,
                height = screen.height - (Calameo.Platform.isChrome() ? 130 : 250),
                title = "";
            this.BookData && (title = this.BookData.name);
            var css = '',
                lib = '',
                script = '',
                bd = "";
            //bd += '<script type="text/javascript">\n', bd += "window.BookData = " + JSON.stringify(this.BookData) + ";\n", bd += "</script>";
            var conf = "";
            //conf += '<script type="text/javascript">\n', conf += "window.Config = " + JSON.stringify(Calameo.EmbedConfig.ViewerConfig) + ";\n", conf += "</script>";
            var domain = window.location.host.replace(/(?:[^.]+\.)?(calameo(?:assets)?\.(?:dev|com))/i, "$1"),
                gac = "";
            //gac += '<script type="text/javascript">\n', gac += 'var domain = "' + domain + '";\n', gac += "var _gaq = _gaq || [];\n", gac += '_gaq.push(["calameo_view._setDomainName", domain]);\n', gac += '_gaq.push(["calameo_event._setDomainName", domain]);\n', gac += "(function() {\n", gac += 'var ga = document.createElement("script"); ga.type = "text/javascript"; ga.async = true;\n', gac += 'ga.src = ("https:" == document.location.protocol ? "https://" : "http://") + "stats.g.doubleclick.net/dc.js";\n', gac += 'var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ga, s);\n', gac += "})();\n", gac += "</script>";
var c = '';
            //var c = "<html><head><title>" + title + '</title><meta http-equiv="content-type" content="text/html; charset=UTF-8" />' + css + gac + bd + conf + '</head><body style="margin:0;padding:0;max-width:100%;max-height:100%;overflow:hidden;">' + lib + script + "</body></html>";
            window.open("data:text/html;base64," + window.btoa(c.replace(/[^\x00-\x7F]/g, "")), Calameo.EmbedConfig.ViewerConfig.bkcode, "width=" + width + ",height=" + height + ",top=20,left=20,scrollbars=no")
        }, this.install = function() {
            var div = document.getElementById("ph-content");
            if (div) {
                this.stat(Calameo.EmbedConfig.ViewerConfig.mode, "install"), div.innerHTML = this.Locales.install, div.style.display = "block";
                var ph = this.placeholder();
                Calameo.Effects.fadeIn(ph, 500), ph.style.display = "block"
            }
            this.showAd()
        }, this.stat = function(type, status) {
            this.BookData && (this.BookData.features.tracking.customer.mini.enabled === !0 && (_gaq.push(["customer_view._setAccount", this.BookData.features.tracking.customer.mini.code]), _gaq.push(["customer_view._trackPageview", this.BookData.features.tracking.customer.mini.url])), this.BookData.features.tracking.calameo.embed.enabled === !0 && (_gaq.push(["calameo_view._setAccount", this.BookData.features.tracking.calameo.mini.code]), _gaq.push(["calameo_view._setCustomVar", 1, "AccountType", this.BookData.account.type || "?", 3]), _gaq.push(["calameo_view._setCustomVar", 2, "TrackerSource", Calameo.EmbedConfig.ViewerConfig.trackersource || "?", 3]), _gaq.push(["calameo_view._setCustomVar", 3, "AccountHistoryStatus", this.BookData.account.status || "?", 3]), _gaq.push(["calameo_view._setCustomVar", 4, "PublishingMode", this.BookData.mode || "?", 3]), _gaq.push(["calameo_view._trackPageview", this.BookData.features.tracking.calameo.mini.url])))
        }, this.loadScript = function(scriptUrl, charset) {
            if ("undefined" == typeof charset && (charset = ""), scriptUrl = scriptUrl.replace(/^http[s]?:/, "https:" == document.location.protocol ? "https:" : "http:"), "undefined" == typeof window.CalameoLoadedScripts) {
                window.CalameoLoadedScripts = "";
                for (var scripts = document.getElementsByTagName("script"), i = 0; i < scripts.length; i++) window.CalameoLoadedScripts += "|" + scripts[i].src
            }
            if (!(window.CalameoLoadedScripts.indexOf(scriptUrl) > 0)) {
                window.CalameoLoadedScripts += "|" + scriptUrl;
                try {
                    var script = document.createElement("script");
                    script.type = "text/javascript", script.src = scriptUrl, script.async = !0, script.charset = charset, document.getElementsByTagName("head")[0].appendChild(script)
                } catch (e) {
                    //document.write('<script type="text/javascript" src="' + scriptUrl + '" charset="utf-8"></script>')
                }
            }
        }, this.loadCss = function(cssUrl) {
            for (var css = document.getElementsByTagName("link"), i = 0; i < css.length; i++)
                if (css[i].href == cssUrl) return;
            try {
                var css = document.createElement("link");
                css.rel = "stylesheet", css.href = cssUrl, css.type = "text/css", css.media = "screen", document.getElementsByTagName("head")[0].appendChild(css)
            } catch (e) {
                document.write('<link rel="stylesheet" href="' + cssUrl + '" type="text/css" media="screen"/>')
            }
        }, this.handleAd = function() {
            var b = document.getElementsByTagName("body")[0];
            if (window.__CalameoAdDisplayCallback = function(detail) {
                    var adc = document.getElementById("adc");
                    if (!adc) return !1;
                    if (window.top.location != window.location && document.referrer.match(/^https?:\/\/(?:[^.]+\.)?calameo(?:assets)?\.(?:com|dev)\//i)) return !1;
                    if (!JSON || !JSON.parse) return !1;
                    var isMobile = null != navigator.userAgent.match(/iPhone/i) || null != navigator.userAgent.match(/iPod/i) || navigator.userAgent.toLowerCase().indexOf("android") > -1,
                        w = window.innerWidth,
                        h = window.innerHeight;
                    if (320 > w || 380 > h) return !1;
                    var customTargeting = JSON.parse(detail);
                    if (!customTargeting) return !1;
                    var adPosition = 100 * Math.random() >= 50 ? "top" : "bottom";
                    b.className = "withad withad-" + adPosition, adSizes = [
                            [728, 90]
                        ], (isMobile ? screen.width : window.innerWidth) < 728 && (adSizes = [
                            [320, 100]
                        ]), adc.style.height = adSizes[0][1] + "px", document.getElementById("vc").style[adPosition] = adSizes[0][1] + "px",
                        function() {
                            /*var gads = document.createElement("script");
                            gads.async = !0, gads.type = "text/javascript";
                            var useSSL = "https:" == document.location.protocol;
                            gads.src = (useSSL ? "https:" : "http:") + "//www.googletagservices.com/tag/js/gpt.js";
                            var node = document.getElementsByTagName("script")[0];
                            node.parentNode.insertBefore(gads, node)*/
                        }(), googletag.cmd.push(function() {
                            googletag.defineSlot("/3858349/Publication_Embed_Leaderboard", adSizes, "adci").addService(googletag.pubads());
                            for (var key in customTargeting) googletag.pubads().setTargeting(key, customTargeting[key]);
                            googletag.pubads().enableSingleRequest(),
                                googletag.enableServices(), googletag.display("adci")
                        });
                    var a = document.createElement("a");
                    a.id = "ad-close", a.href = "javascript:void(0);", a.innerHTML = "&#10006;", a.onclick = function(e) {
                        b.className = ""
                    }, adc.appendChild(a)
                }, this.AdContainer = document.getElementById(Calameo.EmbedConfig.AdContainer), this.AdContainer) {
                if (this.hideAd(), !Calameo.Platform.isIOS() && !Calameo.Platform.isAndroid()) {
                    var a = document.createElement("a");
                    a.id = "ad-close", a.href = "javascript:void(0);", a.innerHTML = "&#10006;";
                    var obj = this;
                    a.onclick = function(e) {
                        b.className = "", obj.closeAd()
                    }, this.AdContainer.appendChild(a)
                }
                this.AdPosition = Math.random() >= .5 ? "top" : "bottom", b.className = "withad withad-" + this.AdPosition
            }
            this.FlyerContainer = document.getElementById(Calameo.EmbedConfig.FlyerContainer), this.FlyerContainer && (b.id = "with-flyer")
        }, this.hideAd = function() {
            this.AdContainer && (this.AdContainer.style.display = "none")
        }, this.showAd = function() {
            this.AdContainer && (this.AdContainer.style.display = "block")
        }, this.closeAd = function() {
            var vc = (document.getElementsByTagName("body")[0], document.getElementById("vc"));
            switch (this.AdPosition) {
                case "bottom":
                    document.body.style.paddingBottom = "0px", vc.style.bottom = "0px";
                    break;
                case "top":
                    document.body.style.paddingTop = "0px", vc.style.top = "0px"
            }
            this.hideAd()
        }, this.setExternalInterfaceHandlers = function() {
            var c = Calameo.EmbedConfig.ViewerConfig;
            if ("undefined" != typeof c.jsapienabled && (parseInt(c.jsapienabled) > 0 || c.jsapienabled === !0 || "true" == c.jsapienabled) && (Calameo.EmbedConfig.__jsAPIEnabled = !0, "undefined" == typeof c.jsapiinitcallback)) {
                var initCallbackFn = Calameo.EmbedConfig.ViewerConfig.jsapiinitcallback = "__initCallback";
                if (obj = this, window[initCallbackFn] = function(view, mode, state, page, relay) {
                        Calameo.EmbedConfig.__jsAPIEnabled = 1 == relay;
                        var onMessageHandler = function(e) {
                            if (Calameo.EmbedConfig.__jsAPIEnabled) {
                                var swf = document.getElementById("cviewer");
                                if (swf) {
                                    let pattern = /([^:]+)\:"([^"]+)"[,]?/gi;
                                    // Assuming 'e.data' is the input data to be parsed
                                    let input = '';
                                    if (typeof e.data === 'string') {
                                        input = DOMPurify.sanitizey(e.data);
                                    }
                                    let pieces;
                                    while ((pieces = pattern.exec(input)) !== null) {
                                        // Ensure 'pieces' has at least two elements before accessing them
                                        if (pieces.length >= 3) {
                                            switch (pieces[1].event) {
                                                case "doAction":
                                                    "function" == typeof swf.doAction && swf.doAction(pieces[2].action);
                                                    break;
                                                case "getPageNumber":
                                                    "function" == typeof swf.getPageNumber && (page = swf.getPageNumber(), window.parent != window && window.postMessage && window.parent.postMessage('event:"page",page:"' + page + '"', "*"));
                                                    break;
                                                case "setPageNumber":
                                                    "function" == typeof swf.setPageNumber && swf.setPageNumber(pieces[2].page)
                                            }
                                        }
                                    }
                                }
                            }
                        };
                        window.attachEvent ? window.attachEvent("onmessage", onMessageHandler, !0) : window.addEventListener ? window.addEventListener("message", onMessageHandler, !0) : window.onmessage = onMessageHandler, Calameo.EmbedConfig.__jsAPIEnabled && window.parent != window && window.postMessage && window.parent.postMessage('event:"init",view:"' + view + '",mode:"' + mode + '",state:"' + state + '",page:"' + page + '"', "*")
                    }, "undefined" == typeof c.jsapichangecallback) {
                    var changeCallbackFn = Calameo.EmbedConfig.ViewerConfig.jsapichangecallback = "__changeCallback";
                    window[changeCallbackFn] = function(type, value) {
                        Calameo.EmbedConfig.__jsAPIEnabled && window.parent != window && window.postMessage && window.parent.postMessage('event:"change",type:"' + type + '",value:"' + value + '"', "*")
                    }
                }
                if ("undefined" == typeof c.jsapimsgcallback) {
                    var msgCallbackFn = Calameo.EmbedConfig.ViewerConfig.jsapimsgcallback = "__msgCallback";
                    window[msgCallbackFn] = function(msg) {
                        Calameo.EmbedConfig.__jsAPIEnabled && window.parent != window && window.postMessage && window.parent.postMessage('event:"message",msg:"' + msg + '"', "*")
                    }
                }
            }
        }, this.getCookie = function(name) {
            var cookie = document.cookie.match(new RegExp("(^|;)\\s*" + escape(name) + "=([^;\\s]*)"));
            return cookie ? unescape(cookie[2]) : null
        }, "object" == typeof config)
        for (i in Calameo.EmbedConfig)
            for (a in config) a.toLowerCase() == i.toLowerCase() && (Calameo.EmbedConfig[i] = config[a]);
    this.launch()
}), "object" != typeof Calameo.Effects && (Calameo.Effects = {
    Stack: [],
    Count: 0,
    setOpacity: function(index, level) {
        if ("undefined" != typeof Calameo.Effects.Stack[index]) {
            var element = Calameo.Effects.Stack[index];
            if ("undefined" != typeof element.style) return element.style.opacity = level, element.style.MozOpacity = level, element.style.KhtmlOpacity = level, "undefined" != typeof element.style.filter && (element.style.filter = "alpha(opacity=" + 100 * level + ");"), element
        }
        return !1
    },
    fadeIn: function(element, duration) {
        var count = Calameo.Effects.Count++;
        Calameo.Effects.Stack[count] = element;
        var steps = Math.round(duration / 50);
        for (i = 0; i <= 1; i += 1 / steps) setTimeout("Calameo.Effects.setOpacity(" + count + "," + i + ")", i * duration)
    },
    fadeOut: function(element, duration) {
        var count = Calameo.Effects.Count++;
        Calameo.Effects.Stack[count] = element;
        var steps = Math.round(duration / 50);
        for (i = 0; i <= 1; i += 1 / steps) setTimeout("Calameo.Effects.setOpacity(" + count + "," + (1 - i) + ")", i * duration)
    }
}), "object" != typeof Calameo.MediaBox && (Calameo.MediaBox = {
    open: function(msg) {
        var regs = msg.match(/^([^:\.]+)(?:\.open)?:([^:]+):(.+)$/i);
        if (regs) {
            var content, flashViewer = document.getElementById("cviewer"),
                ratio = 16 / 9,
                type = regs[1].toLowerCase(),
                provider = regs[2].toLowerCase();
            switch (type) {
                case "audio":
                    switch (provider) {
                        case "soundcloud":
                            var isIE = Calameo.Platform.isIE();
                            if (isIE && 8 >= isIE) content = "Your browser does not support SoundCloud track playback.";
                            else {
                                var url = "https://w.soundcloud.com/player/?url=" + encodeURIComponent(regs[3]) + "&amp;auto_play=true&amp;hide_related=true&amp;show_comments=true&amp;show_user=true&amp;show_reposts=false&amp;visual=true";
                                content = '<iframe id="vm-player" src="' + url + '" type="text/html" scrolling="no" width="100%" height="100%" frameborder="0" allowfullscreen></iframe>'
                            }
                    }
                    break;
                case "video":
                    switch (provider) {
                        case "youtube":
                            var url = "https://www.youtube.com/embed/" + regs[3] + "?origin=calameo.com&autoplay=1&autohide=1&fs=0&modestbranding=1&rel=0&wmode=opaque";
                            content = '<iframe id="yt-player" src="' + url + '" type="text/html" scrolling="no" width="100%" height="100%" frameborder="0"></iframe>';
                            break;
                        case "dailymotion":
                            var url = "https://www.dailymotion.com/embed/video/" + regs[3] + "?origin=calameo.com&autoplay=1&autohide=1&fs=0&logo=0&related=0";
                            content = '<iframe id="dm-player" src="' + url + '" type="text/html" scrolling="no" width="100%" height="100%" frameborder="0"></iframe>';
                            break;
                        case "vimeo":
                            var url = "https://player.vimeo.com/video/" + regs[3] + "?autoplay=1&player_id=vm-player";
                            content = '<iframe id="vm-player" src="' + url + '" type="text/html" scrolling="no" width="100%" height="100%" frameborder="0"></iframe>';
                            break;
                        case "user":
                            var url = regs[3];
                            content = '<video width="100%" height="100%" controls autoplay><source src="' + url + '" type="video/mp4">Your browser does not support the video tag.</video>'
                    }
            }
            content && Calameo.Lightbox.show("", ratio, function(box, container) {
                box.style.transform = "", container.innerHTML = content
            }, function(box, container) {
                container.innerHTML = "", flashViewer && flashViewer.closeLink && flashViewer.closeLink(regs[1])
            })
        }
    }
}), "object" != typeof Calameo.Lightbox && (Calameo.Lightbox = {
    builded: !1,
    overlay: null,
    box: null,
    boxContent: null,
    ratio: 16 / 9,
    showCallback: function() {},
    hideCallback: function() {},
    link: {},
    build: function() {
        var body = document.getElementsByTagName("body")[0];
        with(this.overlay = document.createElement("div"), this.overlay.style) position = "absolute", left = top = right = bottom = "0px", backgroundColor = "#000000", opacity = .75, transition = "opacity 0.5s", WebkitTransition = "opacity 0.5s", MozTransition = "opacity 0.5s", OTransition = "opacity 0.5s";
        with(this.overlay.addEventListener("mousedown", this.onCloseClickHandler), this.box = document.createElement("div"), this.box.style) position = "absolute", left = top = right = bottom = "20px", backgroundColor = "#000000", transition = "opacity 0.5s, transform 0.5s", transformOrigin = "50% 50%", WebkitTransition = "opacity 0.5s, transform 0.5s", WebkitTransformOrigin = "50% 50%", MozTransition = "opacity 0.5s, transform 0.5s", MozTransformOrigin = "50% 50%", OTransition = "opacity 0.5s, transform 0.5s", OTransformOrigin = "50% 50%", borderRadius = "8px", color = "white", textAlign = "left", fontFamily = "Helvetica,Arial,sans-serif";
        with(this.boxContent = document.createElement("div"), this.boxContent.style) position = "absolute", left = top = right = bottom = "16px";
        with(this.closeButton = document.createElement("a"), this.closeButton.style) position = "absolute", display = "block", right = "-10px", top = "-10px", width = "30px", height = "30px", color = "#eeeeee", backgroundColor = "#666666", borderRadius = "15px", lineHeight = "30px", textDecoration = "none", textAlign = "center", fontSize = "18px";
        with(this.closeButton) innerHTML = "&#10006;", href = "javascript:void(0);", addEventListener("click", this.onCloseClickHandler);
        this.box.appendChild(this.boxContent), this.box.appendChild(this.closeButton), this.resize(), this.hide(!0), body.appendChild(this.overlay), body.appendChild(this.box), this.builded = !0
    },
    show: function(content, ratio, showCallback, hideCallback) {
        this.ratio = ratio, this.hideCallback = hideCallback, this.builded ? this.resize() : this.build(), this.boxContent.innerHTML = content, window.addEventListener("resize", this.onWindowResizeHandler);
        var self = this;
        this.box.style.display = "block", this.overlay.style.display = "block", this.box.style.pointerEvents = "auto", this.overlay.style.pointerEvents = "auto", setTimeout(function() {
            self.box.style.msTransform = "scale3d(1,1,1)", self.box.style.transform = "scale3d(1,1,1)", self.box.style.opacity = 1, self.box.style.MsFilter = "progid:DXImageTransform.Microsoft.Alpha(Opacity=100)", self.box.style.filter = "alpha(opacity=100)", self.overlay.style.opacity = .75, self.overlay.style.MsFilter = "progid:DXImageTransform.Microsoft.Alpha(Opacity=75)", self.overlay.style.filter = "alpha(opacity=75)", showCallback(self.box, self.boxContent)
        }, 50)
    },
    hide: function(initial) {
        initial || (initial = !1), this.hideCallback(this.box, this.boxContent), window.removeEventListener("resize", this.onWindowResizeHandler), this.box.style.pointerEvents = "none", this.box.style.WebkitTransform = "scale3d(0.9,0.9,1)", this.box.style.MozTransform = "scale3d(0.9,0.9,1)", this.box.style.transform = "scale3d(0.9,0.9,1)", this.box.style.opacity = 0, this.box.style.MsFilter = "progid:DXImageTransform.Microsoft.Alpha(Opacity=0)", this.box.style.filter = "alpha(opacity=0)", this.overlay.style.pointerEvents = "none", this.overlay.style.opacity = 0, this.overlay.style.MsFilter = "'progid:DXImageTransform.Microsoft.Alpha(Opacity=0)'", this.overlay.style.filter = "alpha(opacity=0)";
        var self = this,
            onHideComplete = function() {
                self.overlay.style.display = "none", self.box.style.display = "none"
            },
            isIE = Calameo.Platform.isIE();
        return initial || isIE && 8 >= isIE ? onHideComplete() : void setTimeout(onHideComplete, 500)
    },
    resize: function() {
        var viewportWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth,
            viewportHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight,
            width = .8 * viewportWidth,
            height = .8 * viewportHeight;
        width / this.ratio > height ? width = height * this.ratio : height = width / this.ratio, this.box.style.left = this.box.style.right = "" + Math.round((viewportWidth - width) / 2) + "px", this.box.style.top = this.box.style.bottom = "" + Math.round((viewportHeight - height) / 2) + "px"
    },
    onWindowResizeHandler: function() {
        Calameo.Lightbox.resize()
    },
    onCloseClickHandler: function(e) {
        return Calameo.Lightbox.hide(), !1
    }
}), "object" != typeof Calameo.Fullscreen && (Calameo.Fullscreen = {
    builded: !1,
    build: function(callback) {
        this.builded || (document.addEventListener("webkitfullscreenchange", function(e) {
            callback(Calameo.Fullscreen.isFullscreen())
        }, !1), document.addEventListener("mozfullscreenchange", function(e) {
            callback(Calameo.Fullscreen.isFullscreen())
        }, !1), document.addEventListener("fullscreenchange", function(e) {
            callback(Calameo.Fullscreen.isFullscreen())
        }, !1), document.addEventListener("MSFullscreenChange", function(e) {
            callback(Calameo.Fullscreen.isFullscreen())
        }, !1), this.builded = !0)
    },
    toggle: function() {
        var result = !1;
        result = this.isFullscreen() ? this.exit() : this.enter()
    },
    isFullscreen: function() {
        try {
            if (window.parent && window.parent != window && (window.parent.document.fullscreenElement || window.parent.document.mozFullScreenEnabled || window.parent.document.webkitFullscreenElement || window.parent.document.msFullscreenElement)) return !0
        } catch (e) {}
        return !!(document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement)
    },
    hasFullscreen: function() {
        return document.fullscreenEnabled || document.mozFullScreenEnabled || document.webkitFullscreenEnabled || document.msFullscreenEnabled
    },
    enter: function(el) {
        if (el || (el = document.documentElement), el.requestFullscreen) el.requestFullscreen();
        else if (el.msRequestFullscreen) el.msRequestFullscreen();
        else if (el.mozRequestFullScreen) el.mozRequestFullScreen();
        else {
            if (!el.webkitRequestFullscreen) return !1;
            el.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT)
        }
        return !0
    },
    exit: function(el) {
        el || (el = document);
        try {
            window.parent && (window.parent.document.fullscreenElement || window.parent.document.mozFullScreenEnabled || window.parent.document.webkitFullscreenElement || window.parent.document.msFullscreenElement) && (el = window.parent.document)
        } catch (e) {}
        if (el.exitFullscreen) el.exitFullscreen();
        else if (el.msExitFullscreen) el.msExitFullscreen();
        else if (el.mozCancelFullScreen) el.mozCancelFullScreen();
        else {
            if (!el.webkitExitFullscreen) return !1;
            el.webkitExitFullscreen()
        }
        return !0
    }
}), "object" != typeof Calameo.Platform && (Calameo.Platform = {
    isSafari: function() {
        return navigator.userAgent.toLowerCase().indexOf("safari") > -1 && navigator.userAgent.toLowerCase().indexOf("chrome") < 0 && !Calameo.Platform.isIOS()
    },
    isIOS: function() {
        return null != navigator.userAgent.match(/iPhone/i) || null != navigator.userAgent.match(/iPad/i) || null != navigator.userAgent.match(/iPod/i)
    },
    isIOSPhone: function() {
        return null != navigator.userAgent.match(/iPhone/i) || null != navigator.userAgent.match(/iPod/i)
    },
    isIOSTablet: function() {
        return null != navigator.userAgent.match(/iPad/i)
    },
    isAndroid: function() {
        return navigator.userAgent.toLowerCase().indexOf("android") > -1
    },
    isAndroidPhone: function() {
        return navigator.userAgent.toLowerCase().indexOf("android") > -1 && window.innerWidth < 600 && window.innerHeight < 600
    },
    isAndroidTablet: function() {
        return navigator.userAgent.toLowerCase().indexOf("android") > -1 && (window.innerWidth > 600 || window.innerHeight > 600)
    },
    isIE: function() {
        var myNav = navigator.userAgent.toLowerCase();
        return -1 != myNav.indexOf("msie") || -1 != myNav.indexOf("trident/") ? parseInt(myNav.split("msie")[1]) : !1
    },
    isChrome: function() {
        return navigator.userAgent.toLowerCase().indexOf("chrome") > -1 && navigator.userAgent.toLowerCase().indexOf("android") < 0
    },
    isChromeForIos: function() {
        return navigator.userAgent.toLowerCase().indexOf("crios") > -1
    },
    isMozilla: function() {
        return navigator.userAgent.toLowerCase().indexOf("firefox") > -1
    },
    isOpera: function() {
        return navigator.userAgent.toLowerCase().indexOf("opera") > -1
    },
    isWindows: function() {
        return navigator.appVersion.toLowerCase().indexOf("windows") > -1
    },
    isMacOS: function() {
        return navigator.appVersion.toLowerCase().indexOf("mac") > -1
    },
    isLinux: function() {
        return navigator.appVersion.toLowerCase().indexOf("x11") > -1 || navigator.appVersion.toLowerCase().indexOf("linux") > -1
    }
}), "object" != typeof Calameo.EmbeddedLocales && (Calameo.EmbeddedLocales = {
    en: {
        install: 'You need to upgrade your Adobe Flash Player to read this publication.<br/><a href="http://get.adobe.com/en/flashplayer/" target="_blank">Download it from Adobe</a>',
        readOnIPad: "Read this publication",
        preparing: "Loading...",
        error: "Publication not found",
        flyer: "Join CalamÃ©o to publish and share documents with the world!",
        button: "Create a free account"
    },
    fr: {
        install: 'Pour lire cette publication, vous devez mettre Ã  niveau Adobe Flash Player.<br/><a href="http://get.adobe.com/fr/flashplayer/" target="_blank">TÃ©lÃ©charger la derniÃ¨re version sur le site d\'Adobe</a>',
        readOnIPad: "Lire cette publication",
        preparing: "Chargement...",
        error: "Publication introuvable",
        flyer: "Rejoingez CalamÃ©o pour publier et partager vos documents !",
        button: "CrÃ©ez un compte gratuit"
    },
    es: {
        install: 'Debe actualizar su Adobe Flash Player para leer esta publicaciÃ³n.<br/><a href="http://get.adobe.com/en/flashplayer/" target="_blank">DescÃ¡rguela del sitio web de Adobe</a>',
        readOnIPad: "Leer esta publicaciÃ³n",
        preparing: "Cargando...",
        error: "No se encontrÃ³ la publicaciÃ³n",
        flyer: "Â¡HÃ¡gase miembro de CalamÃ©o para que pueda publicar y compartir documentos con el mundo!",
        button: "Crear una cuenta gratis"
    },
    de: {
        install: 'Sie mÃ¼ssen Ihren Adobe Flash Player aktualisieren, um diese Publikation lesen zu kÃ¶nnen.<br/><a href="http://get.adobe.com/de/flashplayer/" target="_blank">Download bei Adobe</a>',
        readOnIPad: "Diese Publikation lesen",
        preparing: "Ladevorgang...",
        error: "Publikation nicht gefunden",
        flyer: "Registrieren Sie sich bei CalamÃ©o, um Dokumente weltweit zu verÃ¶ffentlichen und weiterzugeben!",
        button: "Ein kostenloses Konto anlegen"
    },
    pt: {
        install: 'You need to upgrade your Adobe Flash Player to read this publication.<br/><a href="http://get.adobe.com/en/flashplayer/" target="_blank">Download it from Adobe</a>',
        readOnIPad: "Read this publication",
        preparing: "Loading...",
        error: "Publication not found",
        flyer: "Adira ao CalamÃ©o para publicar e partilhar documentos com o mundo!",
        button: "Criar uma conta gratuita"
    },
    it: {
        install: 'Devi aggiornare il tuo Adobe Flash Player per poter leggere questa pubblicazione.<br/><a href="http://get.adobe.com/en/flashplayer/" target="_blank">Scaricalo da Adobe</a>',
        readOnIPad: "Leggi questa pubblicazione",
        preparing: "Sto caricando...",
        error: "Pubblicazione non trovata",
        flyer: "Iscriviti a CalamÃ©o per pubblicare e condividere documenti con tutti!",
        button: "Crea un account gratuito"
    },
    ru: {
        install: 'Ð”Ð»Ñ Ð¿Ñ€Ð¾ÑÐ¼Ð¾Ñ‚Ñ€Ð° Ð´Ð°Ð½Ð½Ð¾Ð¹ Ð¿ÑƒÐ±Ð»Ð¸ÐºÐ°Ñ†Ð¸Ð¸ Ð²Ñ‹ Ð´Ð¾Ð»Ð¶Ð½Ñ‹ Ð¾Ð±Ð½Ð¾Ð²Ð¸Ñ‚ÑŒ Ð²ÐµÑ€ÑÐ¸ÑŽ Adobe Flash Player.<br/><a href="http://get.adobe.com/en/flashplayer/" target="_blank">Ð¡ÐºÐ°Ñ‡Ð°Ñ‚ÑŒ Ð½Ð¾Ð²ÑƒÑŽ Ð²ÐµÑ€ÑÐ¸ÑŽ Ñ Adobe</a>',
        readOnIPad: "ÐŸÑ€Ð¾ÑÐ¼Ð¾Ñ‚Ñ€ÐµÑ‚ÑŒ Ð´Ð°Ð½Ð½ÑƒÑŽ Ð¿ÑƒÐ±Ð»Ð¸ÐºÐ°Ñ†Ð¸ÑŽ",
        preparing: "Ð˜Ð´ÐµÑ‚ Ð·Ð°Ð³Ñ€ÑƒÐ·ÐºÐ°...",
        error: "ÐŸÑƒÐ±Ð»Ð¸ÐºÐ°Ñ†Ð¸Ñ Ð½Ðµ Ð½Ð°Ð¹Ð´ÐµÐ½Ð°",
        flyer: "ÐŸÑ€Ð¸ÑÐ¾ÐµÐ´Ð¸Ð½ÑÐ¹Ñ‚ÐµÑÑŒ Ðº CalamÃ©o, Ñ‡Ñ‚Ð¾Ð±Ñ‹ Ð¿ÑƒÐ±Ð»Ð¸ÐºÐ¾Ð²Ð°Ñ‚ÑŒ ÑÐ²Ð¾Ð¸ Ð´Ð¾ÐºÑƒÐ¼ÐµÐ½Ñ‚Ñ‹ Ð¸ Ð´ÐµÐ»Ð¸Ñ‚ÑŒÑÑ Ð¸Ð¼Ð¸ ÑÐ¾ Ð²ÑÐµÐ¼ Ð¼Ð¸Ñ€Ð¾Ð¼!",
        button: "Ð¡Ð¾Ð·Ð´Ð°Ñ‚ÑŒ Ð±ÐµÑÐ¿Ð»Ð°Ñ‚Ð½Ñ‹Ð¹ Ð°ÐºÐºÐ°ÑƒÐ½Ñ‚"
    },
    jp: {
        install: 'ã“ã®e-ãƒ‰ã‚­ãƒ¥ãƒ¡ãƒ³ãƒˆã‚’è¡¨ç¤ºã™ã‚‹ã«ã¯Adobe Flash Playerã®ã‚¢ãƒƒãƒ—ã‚°ãƒ¬ãƒ¼ãƒ‰ãŒå¿…è¦ã§ã™ã€‚<br/><a href="http://get.adobe.com/en/flashplayer/" target="_blank">Adobeã®ã‚µã‚¤ãƒˆã‹ã‚‰ãƒ€ã‚¦ãƒ³ãƒ­ãƒ¼ãƒ‰</a>',
        readOnIPad: "ã“ã®e-ãƒ‰ã‚­ãƒ¥ãƒ¡ãƒ³ãƒˆã‚’ç«‹ã¡èª­ã¿",
        preparing: "ãƒ­ãƒ¼ãƒ‰ä¸­â€¦",
        error: "e-ãƒ‰ã‚­ãƒ¥ãƒ¡ãƒ³ãƒˆãŒè¦‹ã¤ã‹ã‚Šã¾ã›ã‚“ã§ã—ãŸ",
        flyer: "CalamÃ©o ã«ç™»éŒ²ã—ã¦ãƒ•ã‚¡ã‚¤ãƒ«ã‚’å…¬é–‹ã—ã¦ä¸–ç•Œä¸­ã®ãƒ¦ãƒ¼ã‚¶ãƒ¼ã¨ã‚·ã‚§ã‚¢ï¼",
        button: "ç„¡æ–™ã‚¢ã‚«ã‚¦ãƒ³ãƒˆã‚’ä½œæˆ"
    },
    kr: {
        install: 'ì´ ë°œí–‰ë¬¼ì„ ì½ìœ¼ë ¤ë©´ Adobe Flash Playerë¥¼ ì—…ê·¸ë ˆì´ë“œí•´ì•¼ í•©ë‹ˆë‹¤.<br/><a href="http://get.adobe.com/en/flashplayer/" target="_blank">Adobeì—ì„œ ë‹¤ìš´ë¡œë“œí•˜ì„¸ìš”</a>',
        readOnIPad: "ë°œí–‰ë¬¼ ì½ê¸°",
        preparing: "ë¡œë“œì¤‘...",
        error: "ë°œí–‰ë¬¼ì„ ì°¾ì§€ ëª»í–ˆìŠµë‹ˆë‹¤",
        flyer: "CalamÃ©oì— ê°€ìž…í•˜ì—¬ ì—¬ëŸ¬ë¶„ì˜ ë¬¸ì„œë¥¼ ì „ì„¸ê³„ ë…ìžë“¤ê³¼ ê³µìœ í•˜ì‹­ì‹œì˜¤!",
        button: "ë¬´ë£Œ ê³„ì • ë§Œë“¤ê¸°"
    },
    cn: {
        install: 'æ‚¨éœ€è¦å‡çº§Adobe Flash Playeræ‰èƒ½é˜…è¯»æœ¬åˆŠç‰©.<br/><a href="http://get.adobe.com/en/flashplayer/" target="_blank">ä»ŽAdobeä¸‹è½½</a>',
        readOnIPad: "é˜…è¯»æœ¬åˆŠç‰©",
        preparing: "æ­£åœ¨è½½å…¥...",
        error: "æœªå‘çŽ°åˆŠç‰©",
        flyer: "åŠ å…¥CalamÃ©oï¼Œå‘å¸ƒæ–‡ä»¶å¹¶ä¸Žä¸–ç•Œåˆ†äº«ï¼",
        button: "åˆ›å»ºå…è´¹å¸æˆ·"
    }
});
