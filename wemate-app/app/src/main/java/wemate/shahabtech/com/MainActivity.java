package wemate.shahabtech.com;

import android.Manifest;
import android.app.DownloadManager;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.pm.PackageManager;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.os.Environment;
import android.util.Base64;
import android.util.Log;
import android.view.View;
import android.webkit.CookieManager;
import android.webkit.JavascriptInterface;
import android.webkit.ValueCallback;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceRequest;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.Button;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.ScrollView;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;

import org.json.JSONArray;
import org.json.JSONObject;

import java.net.URLDecoder;

public class MainActivity extends AppCompatActivity {

    private static final String TAG = "MainActivity";
    private static final String PREFS_NAME = "WeMatePrefs";
    private static final int FILE_CHOOSER_REQUEST_CODE = 1001;
    private static final int PERMISSION_REQUEST_CODE = 1002;

    // Hardcoded Server URL
    public static final String SERVER_URL = "https://panel.shahabtech.com";

    private SharedPreferences prefs;

    // UI Layout Containers
    private ScrollView layoutLogin;
    private LinearLayout layoutAccounts;
    private LinearLayout layoutWebview;

    // Login Elements
    private EditText inputUsername, inputPassword;
    private Button btnLogin;
    private ProgressBar loginProgress;

    // Account List Elements
    private TextView txtUserName, txtNoAccounts;
    private Button btnLogout;
    private ProgressBar accountsProgress;
    private LinearLayout containerAccountList;

    // WebView Elements
    private TextView txtActiveAccountName;
    private Button btnSwitchAccount, btnRefreshWebview;
    private ProgressBar webviewProgress;
    private WebView mWebView;

    private ValueCallback<Uri[]> mFilePathCallback;
    private boolean isWarmingUp = true;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        prefs = getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE);

        initViews();
        setupWebView();
        checkPermissions();

        // Warm up WebView by loading the panel domain so Hostinger CDN clearance is obtained
        mWebView.loadUrl(SERVER_URL + "/login");

        String savedUser = prefs.getString("username", "");
        String savedPass = prefs.getString("password", "");
        inputUsername.setText(savedUser);
        inputPassword.setText(savedPass);

        if (prefs.getBoolean("is_logged_in", false) && !savedUser.isEmpty() && !savedPass.isEmpty()) {
            performLogin(savedUser, savedPass, true);
        } else {
            showScreen("LOGIN");
        }
    }

    private void initViews() {
        layoutLogin = findViewById(R.id.layout_login);
        layoutAccounts = findViewById(R.id.layout_accounts);
        layoutWebview = findViewById(R.id.layout_webview);

        inputUsername = findViewById(R.id.input_username);
        inputPassword = findViewById(R.id.input_password);
        btnLogin = findViewById(R.id.btn_login);
        loginProgress = findViewById(R.id.login_progress);

        txtUserName = findViewById(R.id.txt_user_name);
        txtNoAccounts = findViewById(R.id.txt_no_accounts);
        btnLogout = findViewById(R.id.btn_logout);
        accountsProgress = findViewById(R.id.accounts_progress);
        containerAccountList = findViewById(R.id.container_account_list);

        txtActiveAccountName = findViewById(R.id.txt_active_account_name);
        btnSwitchAccount = findViewById(R.id.btn_switch_account);
        btnRefreshWebview = findViewById(R.id.btn_refresh_webview);
        webviewProgress = findViewById(R.id.webview_progress);
        mWebView = findViewById(R.id.webview);

        btnLogin.setOnClickListener(v -> {
            String user = inputUsername.getText().toString().trim();
            String pass = inputPassword.getText().toString().trim();

            if (user.isEmpty() || pass.isEmpty()) {
                Toast.makeText(this, "Please enter Username/Email and Password", Toast.LENGTH_SHORT).show();
                return;
            }

            performLogin(user, pass, false);
        });

        btnLogout.setOnClickListener(v -> {
            prefs.edit().clear().apply();
            CookieManager.getInstance().removeAllCookies(null);
            showScreen("LOGIN");
            Toast.makeText(this, "Logged out successfully", Toast.LENGTH_SHORT).show();
        });

        btnSwitchAccount.setOnClickListener(v -> {
            mWebView.loadUrl("about:blank");
            showScreen("ACCOUNTS");
            loadAssignedAccounts();
        });

        btnRefreshWebview.setOnClickListener(v -> {
            if (mWebView != null) {
                mWebView.reload();
            }
        });
    }

    private void showScreen(String screen) {
        layoutLogin.setVisibility("LOGIN".equals(screen) ? View.VISIBLE : View.GONE);
        layoutAccounts.setVisibility("ACCOUNTS".equals(screen) ? View.VISIBLE : View.GONE);
        layoutWebview.setVisibility("WEBVIEW".equals(screen) ? View.VISIBLE : View.GONE);
    }

    /**
     * Executes login request via WebView Chromium engine to bypass Hostinger CDN bot challenge
     */
    private void performLogin(String user, String pass, boolean isAutoLogin) {
        if (!isAutoLogin) {
            loginProgress.setVisibility(View.VISIBLE);
            btnLogin.setEnabled(false);
        }

        // JavaScript to execute in WebView
        String js = "(function() {" +
                "  fetch('" + SERVER_URL + "/api/extension/login', {" +
                "    method: 'POST'," +
                "    headers: {" +
                "      'Content-Type': 'application/json'," +
                "      'Accept': 'application/json'" +
                "    }," +
                "    body: JSON.stringify({" +
                "      username: " + JSONObject.quote(user) + "," +
                "      password: " + JSONObject.quote(pass) +
                "    })" +
                "  })" +
                "  .then(function(r) { return r.json(); })" +
                "  .then(function(data) {" +
                "    window.AndroidBridge.onLoginResult(JSON.stringify(data), " + JSONObject.quote(user) + ", " + JSONObject.quote(pass) + ");" +
                "  })" +
                "  .catch(function(err) {" +
                "    window.AndroidBridge.onApiError('Login failed: ' + err.message);" +
                "  });" +
                "})();";

        mWebView.post(() -> mWebView.evaluateJavascript(js, null));
    }

    private void loadAssignedAccounts() {
        accountsProgress.setVisibility(View.VISIBLE);
        txtNoAccounts.setVisibility(View.GONE);
        containerAccountList.removeAllViews();

        String js = "(function() {" +
                "  fetch('" + SERVER_URL + "/api/extension/platforms', {" +
                "    method: 'GET'," +
                "    headers: { 'Accept': 'application/json' }" +
                "  })" +
                "  .then(function(r) { return r.json(); })" +
                "  .then(function(data) {" +
                "    window.AndroidBridge.onPlatformsResult(JSON.stringify(data));" +
                "  })" +
                "  .catch(function(err) {" +
                "    window.AndroidBridge.onApiError('Failed to fetch accounts: ' + err.message);" +
                "  });" +
                "})();";

        mWebView.post(() -> mWebView.evaluateJavascript(js, null));
    }

    private void openAccountInWebView(String displayName, int platformId, int accountId, String targetUrl) {
        txtActiveAccountName.setText(displayName);
        showScreen("WEBVIEW");
        webviewProgress.setVisibility(View.VISIBLE);

        String endpoint = SERVER_URL + "/api/extension/cookies/" + platformId + (accountId > 0 ? "/" + accountId : "");

        String js = "(function() {" +
                "  fetch('" + endpoint + "', {" +
                "    method: 'GET'," +
                "    headers: { 'Accept': 'application/json' }" +
                "  })" +
                "  .then(function(r) { return r.json(); })" +
                "  .then(function(data) {" +
                "    window.AndroidBridge.onCookiesResult(JSON.stringify(data), " + JSONObject.quote(targetUrl) + ");" +
                "  })" +
                "  .catch(function(err) {" +
                "    window.AndroidBridge.onApiError('Failed to fetch cookies: ' + err.message);" +
                "  });" +
                "})();";

        mWebView.post(() -> mWebView.evaluateJavascript(js, null));
    }

    private void addAccountCard(String displayName, String platformName, int platformId, int accountId, String targetUrl) {
        LinearLayout card = new LinearLayout(this);
        card.setOrientation(LinearLayout.VERTICAL);
        card.setPadding(32, 28, 32, 28);

        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT,
                LinearLayout.LayoutParams.WRAP_CONTENT
        );
        params.setMargins(0, 0, 0, 20);
        card.setLayoutParams(params);
        card.setBackgroundColor(0xFFFFFFFF);
        card.setElevation(6f);
        card.setClickable(true);
        card.setFocusable(true);

        TextView title = new TextView(this);
        title.setText(displayName);
        title.setTextSize(17);
        title.setTextColor(0xFF0F172A);
        title.setTypeface(null, android.graphics.Typeface.BOLD);

        TextView subtitle = new TextView(this);
        subtitle.setText("Platform: " + platformName + "  •  Click to Open");
        subtitle.setTextSize(13);
        subtitle.setTextColor(0xFF64748B);
        subtitle.setPadding(0, 10, 0, 0);

        card.addView(title);
        card.addView(subtitle);

        card.setOnClickListener(v -> openAccountInWebView(displayName, platformId, accountId, targetUrl));

        containerAccountList.addView(card);
    }

    private void setupWebView() {
        WebSettings webSettings = mWebView.getSettings();
        webSettings.setJavaScriptEnabled(true);
        webSettings.setDomStorageEnabled(true);
        webSettings.setDatabaseEnabled(true);
        webSettings.setAllowFileAccess(true);
        webSettings.setAllowContentAccess(true);
        webSettings.setLoadWithOverviewMode(true);
        webSettings.setUseWideViewPort(true);
        webSettings.setBuiltInZoomControls(true);
        webSettings.setDisplayZoomControls(false);
        webSettings.setMediaPlaybackRequiresUserGesture(false);

        CookieManager cookieManager = CookieManager.getInstance();
        cookieManager.setAcceptCookie(true);
        cookieManager.setAcceptThirdPartyCookies(mWebView, true);

        // Modern Chrome Mobile User-Agent
        String defaultUA = webSettings.getUserAgentString();
        webSettings.setUserAgentString(defaultUA.replace("Android", "Android 14").replace("Mobile", "Mobile"));

        mWebView.addJavascriptInterface(new WebAppInterface(), "AndroidBridge");

        mWebView.setWebViewClient(new WebViewClient() {
            @Override
            public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                return false;
            }

            @Override
            public void onPageFinished(WebView view, String url) {
                super.onPageFinished(view, url);
                webviewProgress.setVisibility(View.GONE);
                if (isWarmingUp) {
                    isWarmingUp = false;
                }
            }
        });

        mWebView.setWebChromeClient(new WebChromeClient() {
            @Override
            public void onProgressChanged(WebView view, int newProgress) {
                if (newProgress < 100) {
                    webviewProgress.setVisibility(View.VISIBLE);
                } else {
                    webviewProgress.setVisibility(View.GONE);
                }
            }

            @Override
            public boolean onShowFileChooser(WebView webView, ValueCallback<Uri[]> filePathCallback, FileChooserParams fileChooserParams) {
                if (mFilePathCallback != null) {
                    mFilePathCallback.onReceiveValue(null);
                }
                mFilePathCallback = filePathCallback;

                Intent intent = fileChooserParams.createIntent();
                try {
                    startActivityForResult(intent, FILE_CHOOSER_REQUEST_CODE);
                } catch (Exception e) {
                    mFilePathCallback = null;
                    Toast.makeText(MainActivity.this, "Cannot open file picker", Toast.LENGTH_SHORT).show();
                    return false;
                }
                return true;
            }
        });

        mWebView.setDownloadListener((url, userAgent, contentDisposition, mimetype, contentLength) -> {
            try {
                DownloadManager.Request request = new DownloadManager.Request(Uri.parse(url));
                request.setMimeType(mimetype);

                String cookies = CookieManager.getInstance().getCookie(url);
                request.addRequestHeader("cookie", cookies);
                request.addRequestHeader("User-Agent", userAgent);

                String filename = URLDecoder.decode(url.substring(url.lastIndexOf("/") + 1), "UTF-8");
                if (filename.contains("?")) {
                    filename = filename.substring(0, filename.indexOf("?"));
                }
                if (!filename.toLowerCase().endsWith(".mp4") && (mimetype.contains("video") || mimetype.contains("mp4"))) {
                    filename = "video_" + System.currentTimeMillis() + ".mp4";
                }

                request.setDescription("Downloading video file...");
                request.setTitle(filename);
                request.allowScanningByMediaScanner();
                request.setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED);
                request.setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, filename);

                DownloadManager dm = (DownloadManager) getSystemService(DOWNLOAD_SERVICE);
                if (dm != null) {
                    dm.enqueue(request);
                    Toast.makeText(getApplicationContext(), "Downloading " + filename + " to Downloads folder...", Toast.LENGTH_LONG).show();
                }
            } catch (Exception e) {
                Toast.makeText(getApplicationContext(), "Download failed: " + e.getLocalizedMessage(), Toast.LENGTH_LONG).show();
            }
        });
    }

    /**
     * JavaScript Bridge Interface connected to WebView Chromium Engine
     */
    public class WebAppInterface {

        @JavascriptInterface
        public void onLoginResult(String jsonStr, String user, String pass) {
            runOnUiThread(() -> {
                loginProgress.setVisibility(View.GONE);
                btnLogin.setEnabled(true);

                try {
                    JSONObject response = new JSONObject(jsonStr);
                    if (response.optBoolean("success", false)) {
                        JSONObject userObj = response.optJSONObject("user");
                        String fullname = userObj != null ? userObj.optString("name", user) : user;

                        prefs.edit()
                                .putString("username", user)
                                .putString("password", pass)
                                .putBoolean("is_logged_in", true)
                                .apply();

                        txtUserName.setText("Welcome, " + fullname + "!");
                        showScreen("ACCOUNTS");
                        loadAssignedAccounts();
                    } else {
                        String msg = response.optString("message", "Login failed");
                        Toast.makeText(MainActivity.this, msg, Toast.LENGTH_LONG).show();
                        showScreen("LOGIN");
                    }
                } catch (Exception e) {
                    Toast.makeText(MainActivity.this, "Response parse error: " + e.getMessage(), Toast.LENGTH_LONG).show();
                    showScreen("LOGIN");
                }
            });
        }

        @JavascriptInterface
        public void onPlatformsResult(String jsonStr) {
            runOnUiThread(() -> {
                accountsProgress.setVisibility(View.GONE);

                try {
                    JSONObject response = new JSONObject(jsonStr);
                    if (response.optBoolean("success", false)) {
                        JSONArray platforms = response.optJSONArray("platforms");

                        if (platforms != null && platforms.length() > 0) {
                            for (int i = 0; i < platforms.length(); i++) {
                                JSONObject platform = platforms.getJSONObject(i);
                                String platformName = platform.optString("name", "Google Flow");
                                String displayName = platform.optString("display_name", platformName);
                                int platformId = platform.optInt("id", 3);
                                int accountId = platform.optInt("account_id", 0);
                                String targetUrl = platform.optString("url", "https://labs.google/fx/tools/flow");

                                addAccountCard(displayName, platformName, platformId, accountId, targetUrl);
                            }
                        } else {
                            txtNoAccounts.setText(response.optString("message", "No assigned accounts found. Contact Administrator."));
                            txtNoAccounts.setVisibility(View.VISIBLE);
                        }
                    } else {
                        txtNoAccounts.setText(response.optString("message", "Failed to fetch accounts"));
                        txtNoAccounts.setVisibility(View.VISIBLE);
                    }
                } catch (Exception e) {
                    txtNoAccounts.setText("Error parsing accounts");
                    txtNoAccounts.setVisibility(View.VISIBLE);
                }
            });
        }

        @JavascriptInterface
        public void onCookiesResult(String jsonStr, String targetUrl) {
            runOnUiThread(() -> {
                try {
                    JSONObject response = new JSONObject(jsonStr);
                    if (response.optBoolean("success", false)) {
                        JSONArray cookies = response.optJSONArray("cookies");
                        JSONObject platformObj = response.optJSONObject("platform");

                        String urlToLoad = targetUrl;
                        if (platformObj != null && platformObj.has("url")) {
                            urlToLoad = platformObj.optString("url", targetUrl);
                        }
                        if (urlToLoad == null || urlToLoad.isEmpty()) {
                            urlToLoad = "https://labs.google/fx/tools/flow";
                        }

                        // Inject Cookies into Android CookieManager for labs.google and .google.com
                        CookieInjector.injectCookies(MainActivity.this, mWebView, cookies, urlToLoad);

                        // Load Google Flow
                        mWebView.loadUrl(urlToLoad);
                    } else {
                        webviewProgress.setVisibility(View.GONE);
                        String msg = response.optString("message", "Failed to load account session");
                        Toast.makeText(MainActivity.this, msg, Toast.LENGTH_LONG).show();
                    }
                } catch (Exception e) {
                    webviewProgress.setVisibility(View.GONE);
                    Toast.makeText(MainActivity.this, "Cookie parse error: " + e.getMessage(), Toast.LENGTH_LONG).show();
                }
            });
        }

        @JavascriptInterface
        public void onApiError(String errorMsg) {
            runOnUiThread(() -> {
                loginProgress.setVisibility(View.GONE);
                btnLogin.setEnabled(true);
                accountsProgress.setVisibility(View.GONE);
                webviewProgress.setVisibility(View.GONE);

                Toast.makeText(MainActivity.this, errorMsg, Toast.LENGTH_LONG).show();
            });
        }
    }

    private void checkPermissions() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            if (ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED) {
                ActivityCompat.requestPermissions(this, new String[]{Manifest.permission.POST_NOTIFICATIONS}, PERMISSION_REQUEST_CODE);
            }
        }
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == FILE_CHOOSER_REQUEST_CODE) {
            if (mFilePathCallback != null) {
                Uri[] results = null;
                if (resultCode == RESULT_OK && data != null) {
                    String dataString = data.getDataString();
                    if (dataString != null) {
                        results = new Uri[]{Uri.parse(dataString)};
                    }
                }
                mFilePathCallback.onReceiveValue(results);
                mFilePathCallback = null;
            }
        }
    }

    @Override
    public void onBackPressed() {
        if (layoutWebview.getVisibility() == View.VISIBLE) {
            if (mWebView.canGoBack()) {
                mWebView.goBack();
            } else {
                showScreen("ACCOUNTS");
            }
        } else if (layoutAccounts.getVisibility() == View.VISIBLE) {
            showScreen("LOGIN");
        } else {
            super.onBackPressed();
        }
    }
}