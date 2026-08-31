package wemate.shahabtech.com;

import android.content.Context;
import android.util.Log;
import android.webkit.CookieManager;
import android.webkit.ValueCallback;
import android.webkit.WebStorage;
import android.webkit.WebView;

import org.json.JSONArray;
import org.json.JSONObject;

public class CookieInjector {

    private static final String TAG = "CookieInjector";

    /**
     * Clears old Google session cookies & WebStorage asynchronously,
     * restores panel API session cookies so API WebView never loses its session,
     * then injects fresh target account cookies and calls onComplete.
     */
    public static void clearAndInjectCookies(Context context, WebView webView, JSONArray cookiesArray, String targetUrl, String panelSessionCookies, String serverUrl, Runnable onComplete) {
        CookieManager cookieManager = CookieManager.getInstance();
        cookieManager.setAcceptCookie(true);

        if (webView != null) {
            cookieManager.setAcceptThirdPartyCookies(webView, true);
            try {
                webView.clearCache(true);
                WebStorage.getInstance().deleteAllData();
            } catch (Exception ignored) {}
        }

        // Asynchronously wipe all old cookies first
        cookieManager.removeAllCookies(new ValueCallback<Boolean>() {
            @Override
            public void onReceiveValue(Boolean value) {
                cookieManager.flush();

                // 1. Restore panel API session cookie so API WebView stays logged in!
                if (panelSessionCookies != null && !panelSessionCookies.isEmpty() && serverUrl != null) {
                    try {
                        String[] parts = panelSessionCookies.split(";");
                        for (String part : parts) {
                            if (!part.trim().isEmpty()) {
                                cookieManager.setCookie(serverUrl, part.trim());
                            }
                        }
                        cookieManager.flush();
                        Log.i(TAG, "Restored panel session cookies to " + serverUrl);
                    } catch (Exception e) {
                        Log.e(TAG, "Error restoring panel cookies: " + e.getMessage());
                    }
                }

                // 2. Inject fresh target account cookies for Google Flow / platform
                if (cookiesArray != null && cookiesArray.length() > 0) {
                    injectCookiesInternal(cookieManager, cookiesArray, targetUrl);
                }

                cookieManager.flush();
                Log.i(TAG, "Fresh account cookies injected and flushed.");

                if (onComplete != null) {
                    onComplete.run();
                }
            }
        });
    }

    public static void injectCookies(Context context, WebView webView, JSONArray cookiesArray, String targetUrl) {
        CookieManager cookieManager = CookieManager.getInstance();
        cookieManager.setAcceptCookie(true);
        if (webView != null) {
            cookieManager.setAcceptThirdPartyCookies(webView, true);
        }
        injectCookiesInternal(cookieManager, cookiesArray, targetUrl);
        cookieManager.flush();
    }

    private static void injectCookiesInternal(CookieManager cookieManager, JSONArray cookiesArray, String targetUrl) {
        if (cookiesArray == null || cookiesArray.length() == 0) return;

        String targetDomain = "labs.google";
        if (targetUrl != null && targetUrl.contains("google.com")) {
            targetDomain = "google.com";
        }

        for (int i = 0; i < cookiesArray.length(); i++) {
            try {
                JSONObject cookieObj = cookiesArray.getJSONObject(i);
                String name = cookieObj.optString("name", cookieObj.optString("key", ""));
                String value = cookieObj.optString("value", cookieObj.optString("val", ""));
                String domain = cookieObj.optString("domain", targetDomain);
                String path = cookieObj.optString("path", "/");
                boolean secure = cookieObj.optBoolean("secure", true);
                boolean httpOnly = cookieObj.optBoolean("httpOnly", false);

                if (name.isEmpty()) continue;

                String cleanDomain = domain;
                if (cleanDomain.startsWith(".")) {
                    cleanDomain = cleanDomain.substring(1);
                }

                StringBuilder cookieString = new StringBuilder();
                cookieString.append(name).append("=").append(value);
                cookieString.append("; Domain=").append(domain);
                cookieString.append("; Path=").append(path);

                if (secure) cookieString.append("; Secure");
                if (httpOnly) cookieString.append("; HttpOnly");

                String urlForCookie = "https://" + cleanDomain + path;
                cookieManager.setCookie(urlForCookie, cookieString.toString());
                if (cleanDomain.contains("google")) {
                    cookieManager.setCookie("https://labs.google" + path, cookieString.toString());
                    cookieManager.setCookie("https://google.com" + path, cookieString.toString());
                }

            } catch (Exception e) {
                Log.e(TAG, "Error parsing cookie item: " + e.getMessage());
            }
        }
    }
}


