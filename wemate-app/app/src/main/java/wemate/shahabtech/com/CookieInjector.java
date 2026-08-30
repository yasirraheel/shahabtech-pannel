package wemate.shahabtech.com;

import android.content.Context;
import android.os.Build;
import android.util.Log;
import android.webkit.CookieManager;
import android.webkit.WebView;

import org.json.JSONArray;
import org.json.JSONObject;

public class CookieInjector {

    private static final String TAG = "CookieInjector";

    public static void injectCookies(Context context, WebView webView, JSONArray cookiesArray, String targetUrl) {
        CookieManager cookieManager = CookieManager.getInstance();
        cookieManager.setAcceptCookie(true);

        if (webView != null) {
            cookieManager.setAcceptThirdPartyCookies(webView, true);
        }

        // Remove old cookies to ensure clean session switching
        cookieManager.removeAllCookies(null);
        cookieManager.flush();

        if (cookiesArray == null || cookiesArray.length() == 0) {
            Log.w(TAG, "No cookies provided for injection.");
            return;
        }

        String targetDomain = ".google.com";
        if (targetUrl != null && targetUrl.contains("labs.google")) {
            targetDomain = "labs.google";
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

                if (domain.startsWith(".")) {
                    domain = domain.substring(1);
                }

                StringBuilder cookieString = new StringBuilder();
                cookieString.append(name).append("=").append(value);
                cookieString.append("; Domain=").append(domain);
                cookieString.append("; Path=").append(path);

                if (secure) {
                    cookieString.append("; Secure");
                }
                if (httpOnly) {
                    cookieString.append("; HttpOnly");
                }

                String urlForCookie = "https://" + domain + path;
                cookieManager.setCookie(urlForCookie, cookieString.toString());
                cookieManager.setCookie("https://labs.google" + path, cookieString.toString());
                cookieManager.setCookie("https://.google.com" + path, cookieString.toString());

            } catch (Exception e) {
                Log.e(TAG, "Error parsing cookie item: " + e.getMessage());
            }
        }

        cookieManager.flush();
        Log.i(TAG, "Cookies successfully injected for " + targetDomain);
    }
}
