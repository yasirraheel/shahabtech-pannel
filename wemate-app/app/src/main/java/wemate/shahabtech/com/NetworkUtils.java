package wemate.shahabtech.com;

import android.os.Handler;
import android.os.Looper;

import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.CookieHandler;
import java.net.CookieManager;
import java.net.CookiePolicy;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.List;
import java.util.Map;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

public class NetworkUtils {

    private static final ExecutorService executor = Executors.newFixedThreadPool(4);
    private static final Handler mainHandler = new Handler(Looper.getMainLooper());
    private static String sessionCookie = "";

    static {
        CookieManager cookieManager = new CookieManager();
        cookieManager.setCookiePolicy(CookiePolicy.ACCEPT_ALL);
        CookieHandler.setDefault(cookieManager);
    }

    public interface ApiCallback {
        void onSuccess(JSONObject response);
        void onError(String errorMessage);
    }

    public static void postRequest(String urlStr, String jsonBody, ApiCallback callback) {
        executor.execute(() -> {
            try {
                URL url = new URL(urlStr);
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("POST");
                conn.setRequestProperty("Content-Type", "application/json");
                conn.setRequestProperty("Accept", "application/json");
                conn.setConnectTimeout(15000);
                conn.setReadTimeout(15000);
                conn.setDoOutput(true);

                if (sessionCookie != null && !sessionCookie.isEmpty()) {
                    conn.setRequestProperty("Cookie", sessionCookie);
                }

                if (jsonBody != null && !jsonBody.isEmpty()) {
                    try (OutputStream os = conn.getOutputStream()) {
                        os.write(jsonBody.getBytes("UTF-8"));
                    }
                }

                int code = conn.getResponseCode();
                saveCookies(conn);

                InputStream is = (code >= 200 && code < 300) ? conn.getInputStream() : conn.getErrorStream();
                String responseStr = readStream(is);

                JSONObject json = new JSONObject(responseStr != null ? responseStr : "{}");

                if (code >= 200 && code < 300) {
                    mainHandler.post(() -> callback.onSuccess(json));
                } else {
                    String msg = json.optString("message", "Request failed with code " + code);
                    mainHandler.post(() -> callback.onError(msg));
                }

            } catch (Exception e) {
                mainHandler.post(() -> callback.onError("Network error: " + e.getLocalizedMessage()));
            }
        });
    }

    public static void getRequest(String urlStr, ApiCallback callback) {
        executor.execute(() -> {
            try {
                URL url = new URL(urlStr);
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("GET");
                conn.setRequestProperty("Accept", "application/json");
                conn.setConnectTimeout(15000);
                conn.setReadTimeout(15000);

                if (sessionCookie != null && !sessionCookie.isEmpty()) {
                    conn.setRequestProperty("Cookie", sessionCookie);
                }

                int code = conn.getResponseCode();
                saveCookies(conn);

                InputStream is = (code >= 200 && code < 300) ? conn.getInputStream() : conn.getErrorStream();
                String responseStr = readStream(is);

                JSONObject json = new JSONObject(responseStr != null ? responseStr : "{}");

                if (code >= 200 && code < 300) {
                    mainHandler.post(() -> callback.onSuccess(json));
                } else {
                    String msg = json.optString("message", "Request failed with code " + code);
                    mainHandler.post(() -> callback.onError(msg));
                }

            } catch (Exception e) {
                mainHandler.post(() -> callback.onError("Network error: " + e.getLocalizedMessage()));
            }
        });
    }

    private static void saveCookies(HttpURLConnection conn) {
        Map<String, List<String>> headerFields = conn.getHeaderFields();
        List<String> cookiesHeader = headerFields.get("Set-Cookie");
        if (cookiesHeader != null) {
            StringBuilder sb = new StringBuilder();
            for (String cookie : cookiesHeader) {
                if (sb.length() > 0) sb.append("; ");
                sb.append(cookie.split(";")[0]);
            }
            if (sb.length() > 0) {
                sessionCookie = sb.toString();
            }
        }
    }

    private static String readStream(InputStream is) {
        if (is == null) return "";
        try (BufferedReader reader = new BufferedReader(new InputStreamReader(is, "UTF-8"))) {
            StringBuilder sb = new StringBuilder();
            String line;
            while ((line = reader.readLine()) != null) {
                sb.append(line);
            }
            return sb.toString();
        } catch (Exception e) {
            return "";
        }
    }

    public static void clearSession() {
        sessionCookie = "";
    }
}
