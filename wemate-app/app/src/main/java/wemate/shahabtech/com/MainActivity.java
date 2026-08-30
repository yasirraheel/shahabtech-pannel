package wemate.shahabtech.com;

import android.Manifest;
import android.app.DownloadManager;
import android.content.ContentResolver;
import android.content.ContentValues;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.pm.PackageManager;
import android.graphics.Color;
import android.graphics.drawable.GradientDrawable;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.os.Environment;
import android.provider.MediaStore;
import java.io.File;
import java.io.OutputStream;
import android.util.Log;
import android.util.TypedValue;
import android.view.Gravity;
import android.view.MenuItem;
import android.view.View;
import android.view.Window;
import android.view.WindowManager;
import android.webkit.CookieManager;
import android.webkit.JavascriptInterface;
import android.webkit.ValueCallback;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceRequest;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.FrameLayout;
import android.widget.ImageButton;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;
import android.widget.ScrollView;
import android.widget.TextView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.ActionBarDrawerToggle;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;
import androidx.core.view.GravityCompat;
import androidx.drawerlayout.widget.DrawerLayout;

import com.google.android.material.appbar.MaterialToolbar;
import com.google.android.material.bottomnavigation.BottomNavigationView;
import com.google.android.material.navigation.NavigationView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.net.URLDecoder;

public class MainActivity extends AppCompatActivity
        implements NavigationView.OnNavigationItemSelectedListener {

    private static final String TAG = "MainActivity";
    private static final String PREFS_NAME = "WeMatePrefs";
    private static final int FILE_CHOOSER_REQUEST_CODE = 1001;
    private static final int PERMISSION_REQUEST_CODE = 1002;

    // Hardcoded Server URL
    public static final String SERVER_URL = "https://panel.shahabtech.com";

    private SharedPreferences prefs;

    // Drawer / Navigation
    private DrawerLayout drawerLayout;
    private NavigationView navigationView;
    private MaterialToolbar toolbar;
    private BottomNavigationView bottomNav;
    private ActionBarDrawerToggle drawerToggle;

    // Drawer header views
    private TextView drawerUserName, drawerUserEmail, drawerAvatarInitial, drawerUserValidity;

    // UI Layout Containers
    private ScrollView layoutLogin;
    private LinearLayout layoutAccounts;
    private LinearLayout layoutWebview;

    // Login Elements
    private EditText inputUsername, inputPassword;
    private CheckBox checkboxRememberMe;
    private Button btnLogin;
    private ProgressBar loginProgress;

    // Account List Elements
    private TextView txtUserName, txtUserAvatar, txtUserValidity, txtNoAccounts;
    private LinearLayout containerValidity;
    private ProgressBar accountsProgress;
    private LinearLayout containerAccountList;
    private LinearLayout cardNoAccounts;

    // Saved Projects Elements
    private LinearLayout containerProjectsList;
    private TextView txtProjectsCount;
    private TextView txtNoProjects;
    private LinearLayout cardNoProjects;

    // WebView Elements
    private TextView txtActiveAccountName;
    private ImageButton btnSwitchAccount, btnRefreshWebview;
    private ProgressBar webviewProgress;
    private WebView mWebView;       // For Google Flow
    private WebView mApiWebView;    // Dedicated hidden WebView for Panel API calls

    // Custom Flow Loaders
    private RelativeLayout layoutWebviewLoader;
    private TextView txtLoaderTitle, txtLoaderSubtitle, txtLoaderPercent;
    private ProgressBar loaderProgressBar;
    private com.google.android.material.card.MaterialCardView cardActionLoader;
    private TextView txtActionLoaderMsg;

    private ValueCallback<Uri[]> mFilePathCallback;
    private String currentUserName = "";
    private String currentValidityText = "";
    private boolean currentIsExpired = false;
    private boolean isRefreshingAccounts = false;

    private boolean isApiWebViewReady = false;
    private final java.util.List<Runnable> pendingApiActions = new java.util.ArrayList<>();

    private void runWhenApiReady(Runnable action) {
        runOnUiThread(() -> {
            if (isApiWebViewReady) {
                action.run();
            } else {
                pendingApiActions.add(action);
            }
        });
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        prefs = getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE);

        initViews();
        setupDrawer();
        setupBottomNav();
        setupApiWebView();
        setupFlowWebView();
        checkPermissions();

        // Restore saved login info
        String savedUser = prefs.getString("saved_username", "");
        String savedPass = prefs.getString("saved_password", "");
        boolean rememberMe = prefs.getBoolean("remember_me", true);

        inputUsername.setText(savedUser);
        inputPassword.setText(savedPass);
        checkboxRememberMe.setChecked(rememberMe);

        // Check if already logged in
        if (prefs.getBoolean("is_logged_in", false) && !savedUser.isEmpty() && !savedPass.isEmpty()) {
            currentUserName = prefs.getString("fullname", savedUser);
            currentValidityText = prefs.getString("validity_text", "");
            currentIsExpired = prefs.getBoolean("is_expired", false);
            updateUserInfo(currentUserName, prefs.getString("email", savedUser), currentValidityText, currentIsExpired);

            showScreen("ACCOUNTS");
            // Render cached accounts instantly
            renderCachedAccounts();
            // Silently verify & update in background
            performLogin(savedUser, savedPass, true);
        } else {
            showScreen("LOGIN");
        }
    }

    private void initViews() {
        drawerLayout = findViewById(R.id.drawer_layout);
        navigationView = findViewById(R.id.nav_view);
        toolbar = findViewById(R.id.toolbar);
        bottomNav = findViewById(R.id.bottom_nav);

        layoutLogin = findViewById(R.id.layout_login);
        layoutAccounts = findViewById(R.id.layout_accounts);
        layoutWebview = findViewById(R.id.layout_webview);

        inputUsername = findViewById(R.id.input_username);
        inputPassword = findViewById(R.id.input_password);
        checkboxRememberMe = findViewById(R.id.checkbox_remember_me);
        btnLogin = findViewById(R.id.btn_login);
        loginProgress = findViewById(R.id.login_progress);

        txtUserName = findViewById(R.id.txt_user_name);
        txtUserAvatar = findViewById(R.id.txt_user_avatar);
        txtUserValidity = findViewById(R.id.txt_user_validity);
        containerValidity = findViewById(R.id.container_validity);
        txtNoAccounts = findViewById(R.id.txt_no_accounts);
        accountsProgress = findViewById(R.id.accounts_progress);
        containerAccountList = findViewById(R.id.container_account_list);
        cardNoAccounts = findViewById(R.id.card_no_accounts);

        containerProjectsList = findViewById(R.id.container_projects_list);
        txtProjectsCount = findViewById(R.id.txt_projects_count);
        txtNoProjects = findViewById(R.id.txt_no_projects);
        cardNoProjects = findViewById(R.id.card_no_projects);

        txtActiveAccountName = findViewById(R.id.txt_active_account_name);
        btnSwitchAccount = findViewById(R.id.btn_switch_account);
        btnRefreshWebview = findViewById(R.id.btn_refresh_webview);
        webviewProgress = findViewById(R.id.webview_progress);
        mWebView = findViewById(R.id.webview);
        mApiWebView = findViewById(R.id.api_webview);

        // Custom Loader Views
        layoutWebviewLoader = findViewById(R.id.layout_webview_loader);
        txtLoaderTitle = findViewById(R.id.txt_loader_title);
        txtLoaderSubtitle = findViewById(R.id.txt_loader_subtitle);
        txtLoaderPercent = findViewById(R.id.txt_loader_percent);
        loaderProgressBar = findViewById(R.id.loader_progress_bar);
        cardActionLoader = findViewById(R.id.card_action_loader);
        txtActionLoaderMsg = findViewById(R.id.txt_action_loader_msg);

        // Drawer header
        View headerView = navigationView.getHeaderView(0);
        drawerUserName = headerView.findViewById(R.id.drawer_user_name);
        drawerUserEmail = headerView.findViewById(R.id.drawer_user_email);
        drawerAvatarInitial = headerView.findViewById(R.id.drawer_avatar_initial);
        drawerUserValidity = headerView.findViewById(R.id.drawer_user_validity);

        // Login button
        btnLogin.setOnClickListener(v -> {
            String user = inputUsername.getText().toString().trim();
            String pass = inputPassword.getText().toString().trim();

            if (user.isEmpty() || pass.isEmpty()) {
                Toast.makeText(this, "Please enter Username/Email and Password", Toast.LENGTH_SHORT).show();
                return;
            }

            // Save Remember Me preference
            boolean remember = checkboxRememberMe.isChecked();
            prefs.edit()
                    .putString("saved_username", remember ? user : "")
                    .putString("saved_password", remember ? pass : "")
                    .putBoolean("remember_me", remember)
                    .apply();

            performLogin(user, pass, false);
        });

        // WebView navigation buttons
        btnSwitchAccount.setOnClickListener(v -> {
            showScreen("ACCOUNTS");
            refreshDashboardData();
        });

        btnRefreshWebview.setOnClickListener(v -> {
            if (mWebView != null) {
                mWebView.reload();
            }
        });
    }

    private void setupDrawer() {
        setSupportActionBar(toolbar);

        drawerToggle = new ActionBarDrawerToggle(
                this, drawerLayout, toolbar,
                R.string.app_name, R.string.app_name
        );
        drawerLayout.addDrawerListener(drawerToggle);
        drawerToggle.syncState();
        drawerToggle.getDrawerArrowDrawable().setColor(getResources().getColor(R.color.on_primary, getTheme()));

        toolbar.setOnClickListener(v -> {
            Toast.makeText(this, "Refreshing dashboard...", Toast.LENGTH_SHORT).show();
            refreshDashboardData();
        });

        navigationView.setNavigationItemSelectedListener(this);
    }

    private void setupBottomNav() {
        bottomNav.setOnItemSelectedListener(item -> {
            int id = item.getItemId();
            if (id == R.id.bottom_home || id == R.id.bottom_accounts) {
                showScreen("ACCOUNTS");
                Toast.makeText(this, "Refreshing accounts...", Toast.LENGTH_SHORT).show();
                refreshDashboardData();
                return true;
            } else if (id == R.id.bottom_settings) {
                Toast.makeText(this, "Settings coming soon!", Toast.LENGTH_SHORT).show();
                return true;
            }
            return false;
        });

        bottomNav.setOnItemReselectedListener(item -> {
            int id = item.getItemId();
            if (id == R.id.bottom_home || id == R.id.bottom_accounts) {
                showScreen("ACCOUNTS");
                Toast.makeText(this, "Refreshing accounts...", Toast.LENGTH_SHORT).show();
                refreshDashboardData();
            } else if (id == R.id.bottom_settings) {
                Toast.makeText(this, "Settings coming soon!", Toast.LENGTH_SHORT).show();
            }
        });
    }

    @Override
    public boolean onNavigationItemSelected(@NonNull MenuItem item) {
        int id = item.getItemId();

        if (id == R.id.nav_home || id == R.id.nav_accounts) {
            showScreen("ACCOUNTS");
            refreshDashboardData();
        } else if (id == R.id.nav_settings) {
            Toast.makeText(this, "Settings coming soon!", Toast.LENGTH_SHORT).show();
        } else if (id == R.id.nav_logout) {
            performLogout();
        }

        drawerLayout.closeDrawer(GravityCompat.START);
        return true;
    }

    private void performLogout() {
        // Clear active session flag only, keep saved credentials for remember me
        prefs.edit()
                .putBoolean("is_logged_in", false)
                .putString("cached_platforms", "")
                .apply();

        CookieManager.getInstance().removeAllCookies(null);
        currentUserName = "";

        // Keep saved credentials in input fields if remember me was on
        String savedUser = prefs.getString("saved_username", "");
        String savedPass = prefs.getString("saved_password", "");
        inputUsername.setText(savedUser);
        inputPassword.setText(savedPass);
        checkboxRememberMe.setChecked(prefs.getBoolean("remember_me", true));

        showScreen("LOGIN");
        Toast.makeText(this, "Logged out successfully", Toast.LENGTH_SHORT).show();
    }

    /**
     * Dynamically updates status bar color and icon tint to match the current screen
     */
    private void updateStatusBarColor(int color, boolean lightIcons) {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            Window window = getWindow();
            window.addFlags(WindowManager.LayoutParams.FLAG_DRAWS_SYSTEM_BAR_BACKGROUNDS);
            window.setStatusBarColor(color);

            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                View decor = window.getDecorView();
                int flags = decor.getSystemUiVisibility();
                if (!lightIcons) {
                    flags |= View.SYSTEM_UI_FLAG_LIGHT_STATUS_BAR;
                } else {
                    flags &= ~View.SYSTEM_UI_FLAG_LIGHT_STATUS_BAR;
                }
                decor.setSystemUiVisibility(flags);
            }
        }
    }

    private void showScreen(String screen) {
        layoutLogin.setVisibility("LOGIN".equals(screen) ? View.VISIBLE : View.GONE);
        layoutAccounts.setVisibility("ACCOUNTS".equals(screen) ? View.VISIBLE : View.GONE);
        layoutWebview.setVisibility("WEBVIEW".equals(screen) ? View.VISIBLE : View.GONE);

        if ("LOGIN".equals(screen)) {
            drawerLayout.setDrawerLockMode(DrawerLayout.LOCK_MODE_LOCKED_CLOSED);
            // Light background status bar with dark icons
            updateStatusBarColor(0xFFF8FAFC, false);
        } else if ("ACCOUNTS".equals(screen)) {
            drawerLayout.setDrawerLockMode(DrawerLayout.LOCK_MODE_UNLOCKED);
            // Primary Indigo status bar (#6366F1) matching MaterialToolbar
            updateStatusBarColor(0xFF6366F1, true);
            renderSavedProjects();
        } else if ("WEBVIEW".equals(screen)) {
            drawerLayout.setDrawerLockMode(DrawerLayout.LOCK_MODE_LOCKED_CLOSED);
            // Dark Slate status bar (#1E293B) matching WebView Header bar
            updateStatusBarColor(0xFF1E293B, true);
        }
    }

    private void updateUserInfo(String fullname, String email, String validityText, boolean isExpired) {
        currentUserName = fullname;
        currentValidityText = validityText;
        currentIsExpired = isExpired;

        txtUserName.setText("Hi, " + fullname + "!");

        String initial = fullname.isEmpty() ? "W" : fullname.substring(0, 1).toUpperCase();
        txtUserAvatar.setText(initial);

        // Validity badge update
        if (validityText != null && !validityText.isEmpty()) {
            txtUserValidity.setText(validityText);
            drawerUserValidity.setText(validityText);

            int bgCol = isExpired ? 0xFFFEE2E2 : 0xFFFEF3C7;
            int textCol = isExpired ? 0xFFB91C1C : 0xFFB45309;

            GradientDrawable badge = new GradientDrawable();
            badge.setColor(bgCol);
            badge.setCornerRadius(dpToPx(8));
            badge.setStroke(dpToPx(1), isExpired ? 0xFFFECACA : 0xFFFDE68A);

            containerValidity.setBackground(badge);
            drawerUserValidity.setBackground(badge);
            txtUserValidity.setTextColor(textCol);
            drawerUserValidity.setTextColor(textCol);

            containerValidity.setVisibility(View.VISIBLE);
            drawerUserValidity.setVisibility(View.VISIBLE);
        } else {
            containerValidity.setVisibility(View.GONE);
            drawerUserValidity.setVisibility(View.GONE);
        }

        // Drawer header
        drawerUserName.setText(fullname);
        drawerUserEmail.setText(email != null ? email : "");
        drawerAvatarInitial.setText(initial);
    }

    /**
     * Dedicated background WebView for all API requests to panel.shahabtech.com.
     * Always stays on panel domain, ensuring 100% same-origin, bypasses CDN challenge,
     * and NEVER suffers from CORS or cross-origin failures.
     */
    private void setupApiWebView() {
        WebSettings ws = mApiWebView.getSettings();
        ws.setJavaScriptEnabled(true);
        ws.setDomStorageEnabled(true);
        ws.setCacheMode(WebSettings.LOAD_NO_CACHE);

        CookieManager cm = CookieManager.getInstance();
        cm.setAcceptCookie(true);
        cm.setAcceptThirdPartyCookies(mApiWebView, true);

        mApiWebView.setWebViewClient(new WebViewClient() {
            @Override
            public void onPageFinished(WebView view, String url) {
                super.onPageFinished(view, url);
                Log.d(TAG, "API WebView ready at: " + url);
                isApiWebViewReady = true;
                for (Runnable r : new java.util.ArrayList<>(pendingApiActions)) {
                    r.run();
                }
                pendingApiActions.clear();
            }
        });
        mApiWebView.addJavascriptInterface(new ApiBridge(), "AndroidBridge");
        mApiWebView.loadUrl(SERVER_URL + "/login");
    }

    private void performLogin(String user, String pass, boolean isAutoLogin) {
        if (!isAutoLogin) {
            loginProgress.setVisibility(View.VISIBLE);
            btnLogin.setEnabled(false);
        }

        runWhenApiReady(() -> {
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

            mApiWebView.post(() -> mApiWebView.evaluateJavascript(js, null));
        });
    }

    private void loadAssignedAccounts() {
        if (accountsProgress != null) {
            accountsProgress.setVisibility(View.VISIBLE);
        }
        if (txtNoAccounts != null) {
            txtNoAccounts.setVisibility(View.GONE);
        }

        runWhenApiReady(() -> {
            String js = "(function() {" +
                    "  fetch('" + SERVER_URL + "/api/extension/platforms?_t=' + new Date().getTime(), {" +
                    "    method: 'GET'," +
                    "    cache: 'no-store'," +
                    "    headers: {" +
                    "      'Accept': 'application/json'," +
                    "      'Cache-Control': 'no-cache, no-store, must-revalidate'," +
                    "      'Pragma': 'no-cache'" +
                    "    }" +
                    "  })" +
                    "  .then(function(r) { return r.json(); })" +
                    "  .then(function(data) {" +
                    "    window.AndroidBridge.onPlatformsResult(JSON.stringify(data));" +
                    "  })" +
                    "  .catch(function(err) {" +
                    "    window.AndroidBridge.onApiError('Failed to fetch accounts: ' + err.message);" +
                    "  });" +
                    "})();";

            mApiWebView.post(() -> mApiWebView.evaluateJavascript(js, null));
        });
    }

    private void renderCachedAccounts() {
        String cached = prefs.getString("cached_platforms", "");
        if (!cached.isEmpty()) {
            displayPlatformsJson(cached);
        }
    }

    private void openAccountInWebView(String displayName, int platformId, int accountId, String targetUrl) {
        int previousAccountId = prefs.getInt("active_account_id", 0);
        if (accountId > 0 && previousAccountId > 0 && previousAccountId != accountId) {
            // Switching to a different assigned account, clear session cookies cleanly
            CookieManager.getInstance().removeAllCookies(null);
            CookieManager.getInstance().flush();
        }
        if (accountId > 0) {
            prefs.edit().putInt("active_account_id", accountId).apply();
        }

        if (platformId > 0) {
            prefs.edit().putInt("flow_platform_id", platformId).putInt("flow_account_id", accountId).apply();
        }
        txtActiveAccountName.setText(displayName);
        showScreen("WEBVIEW");

        // Always target fresh Flow homepage when opening assigned account
        final String finalTargetUrl = (targetUrl == null || targetUrl.isEmpty()) ? "https://labs.google/fx/tools/flow" : targetUrl;

        // Immediately inject cached cookies so session is 100% active before page load
        String cookieKey = "cached_cookies_" + platformId + "_" + accountId;
        String cachedCookies = prefs.getString(cookieKey, prefs.getString("cached_flow_cookies", ""));
        if (!cachedCookies.isEmpty()) {
            try {
                CookieInjector.injectCookies(this, mWebView, new JSONArray(cachedCookies), finalTargetUrl);
            } catch (Exception ignored) {}
        }

        // Always load fresh URL immediately
        mWebView.loadUrl(finalTargetUrl);

        // Fetch latest cookies in background
        fetchCookiesInBackground(platformId, accountId, finalTargetUrl);
    }

    private void fetchCookiesInBackground(int platformId, int accountId, String targetUrl) {
        if (platformId <= 0) return;
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
                "    /* silent in background */" +
                "  });" +
                "})();";

        mApiWebView.post(() -> mApiWebView.evaluateJavascript(js, null));
    }

    private void displayPlatformsJson(String jsonStr) {
        try {
            JSONObject response = new JSONObject(jsonStr);
            if (response.optBoolean("success", false)) {
                String validity = response.optString("validity_text", "");
                boolean isExpired = response.optBoolean("is_expired", false);
                if (!validity.isEmpty()) {
                    updateUserInfo(currentUserName, prefs.getString("email", ""), validity, isExpired);
                }

                JSONArray platforms = response.optJSONArray("platforms");
                containerAccountList.removeAllViews();

                if (platforms != null && platforms.length() > 0) {
                    if (cardNoAccounts != null) cardNoAccounts.setVisibility(View.GONE);
                    containerAccountList.setVisibility(View.VISIBLE);
                    for (int i = 0; i < platforms.length(); i++) {
                        JSONObject platform = platforms.getJSONObject(i);
                        String platformName = platform.optString("name", "Google Flow");
                        String displayName = platform.optString("display_name", platform.optString("title", platformName));
                        int platformId = platform.optInt("id", 3);
                        int accountId = platform.optInt("account_id", 0);
                        String targetUrl = platform.optString("url", "https://labs.google/fx/tools/flow");

                        addAccountCard(displayName, platformName, platformId, accountId, targetUrl);
                    }
                } else {
                    containerAccountList.removeAllViews();
                    containerAccountList.setVisibility(View.GONE);
                    if (cardNoAccounts != null) {
                        String msg = response.optString("message", "No active accounts assigned yet.\nPlease contact your administrator to get access.");
                        if (txtNoAccounts != null) txtNoAccounts.setText(msg);
                        cardNoAccounts.setVisibility(View.VISIBLE);
                    }
                }
            }
        } catch (Exception e) {
            Log.e(TAG, "Error displaying platforms: " + e.getMessage());
        }
    }

    private void addAccountCard(String displayName, String platformName, int platformId, int accountId, String targetUrl) {
        int dp4 = dpToPx(4);
        int dp8 = dpToPx(8);
        int dp12 = dpToPx(12);
        int dp14 = dpToPx(14);
        int dp16 = dpToPx(16);
        int dp44 = dpToPx(44);

        LinearLayout card = new LinearLayout(this);
        card.setOrientation(LinearLayout.HORIZONTAL);
        card.setGravity(Gravity.CENTER_VERTICAL);
        card.setPadding(dp16, dp16, dp16, dp16);

        LinearLayout.LayoutParams cardParams = new LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT,
                LinearLayout.LayoutParams.WRAP_CONTENT
        );
        cardParams.setMargins(0, 0, 0, dp12);
        card.setLayoutParams(cardParams);
        card.setBackground(ContextCompat.getDrawable(this, R.drawable.bg_card_ripple));
        card.setElevation(dpToPx(2));
        card.setClickable(true);
        card.setFocusable(true);

        // Avatar Circle
        FrameLayout avatarFrame = new FrameLayout(this);
        LinearLayout.LayoutParams avatarParams = new LinearLayout.LayoutParams(dp44, dp44);
        avatarFrame.setLayoutParams(avatarParams);
        avatarFrame.setBackground(ContextCompat.getDrawable(this, R.drawable.bg_avatar_circle));

        TextView avatarText = new TextView(this);
        FrameLayout.LayoutParams avatarTextParams = new FrameLayout.LayoutParams(
                FrameLayout.LayoutParams.MATCH_PARENT,
                FrameLayout.LayoutParams.MATCH_PARENT
        );
        avatarText.setLayoutParams(avatarTextParams);
        avatarText.setGravity(Gravity.CENTER);
        String initial = displayName.isEmpty() ? "G" : displayName.substring(0, 1).toUpperCase();
        avatarText.setText(initial);
        avatarText.setTextSize(TypedValue.COMPLEX_UNIT_SP, 18);
        avatarText.setTextColor(getResources().getColor(R.color.primary, getTheme()));
        avatarText.setTypeface(null, android.graphics.Typeface.BOLD);
        avatarFrame.addView(avatarText);

        // Text Column
        LinearLayout textCol = new LinearLayout(this);
        textCol.setOrientation(LinearLayout.VERTICAL);
        LinearLayout.LayoutParams textColParams = new LinearLayout.LayoutParams(
                0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f
        );
        textColParams.setMarginStart(dp14);
        textCol.setLayoutParams(textColParams);

        TextView title = new TextView(this);
        title.setText(displayName);
        title.setTextSize(TypedValue.COMPLEX_UNIT_SP, 15);
        title.setTextColor(getResources().getColor(R.color.text_primary, getTheme()));
        title.setTypeface(null, android.graphics.Typeface.BOLD);
        title.setMaxLines(1);
        title.setEllipsize(android.text.TextUtils.TruncateAt.END);

        TextView subtitle = new TextView(this);
        subtitle.setText(platformName + "  •  Tap to open");
        subtitle.setTextSize(TypedValue.COMPLEX_UNIT_SP, 12);
        subtitle.setTextColor(getResources().getColor(R.color.text_muted, getTheme()));
        subtitle.setPadding(0, dp4, 0, 0);

        textCol.addView(title);
        textCol.addView(subtitle);

        // Status badge
        TextView badge = new TextView(this);
        badge.setText("Active");
        badge.setTextSize(TypedValue.COMPLEX_UNIT_SP, 11);
        badge.setTextColor(getResources().getColor(R.color.badge_green_text, getTheme()));
        badge.setPadding(dp8, dp4, dp8, dp4);
        GradientDrawable badgeBg = new GradientDrawable();
        badgeBg.setColor(getResources().getColor(R.color.badge_green_bg, getTheme()));
        badgeBg.setCornerRadius(dpToPx(6));
        badge.setBackground(badgeBg);
        badge.setTypeface(null, android.graphics.Typeface.BOLD);

        card.addView(avatarFrame);
        card.addView(textCol);
        card.addView(badge);

        card.setOnClickListener(v -> openAccountInWebView(displayName, platformId, accountId, targetUrl));

        containerAccountList.addView(card);
    }

    private int dpToPx(int dp) {
        return (int) TypedValue.applyDimension(
                TypedValue.COMPLEX_UNIT_DIP, dp,
                getResources().getDisplayMetrics()
        );
    }

    public void saveUserProject(String projectId, String projectName, String projectUrl) {
        if (projectId == null || projectId.isEmpty() || projectUrl == null || projectUrl.isEmpty()) return;
        try {
            String json = prefs.getString("flow_saved_projects", "[]");
            JSONArray arr = new JSONArray(json);
            JSONArray updated = new JSONArray();

            String name = (projectName != null && !projectName.trim().isEmpty() && !projectName.equalsIgnoreCase("Google Flow")) ? projectName.trim() : "Untitled Project";

            JSONObject newObj = new JSONObject();
            newObj.put("id", projectId);
            newObj.put("name", name);
            newObj.put("url", projectUrl);
            newObj.put("updatedAt", System.currentTimeMillis());

            updated.put(newObj);

            for (int i = 0; i < arr.length(); i++) {
                JSONObject obj = arr.getJSONObject(i);
                if (!projectId.equals(obj.optString("id"))) {
                    updated.put(obj);
                }
            }

            prefs.edit().putString("flow_saved_projects", updated.toString()).apply();
            runOnUiThread(this::renderSavedProjects);
        } catch (Exception e) {
            Log.e(TAG, "Save project error: ", e);
        }
    }

    public void deleteSavedProject(String projectId) {
        try {
            String json = prefs.getString("flow_saved_projects", "[]");
            JSONArray arr = new JSONArray(json);
            JSONArray updated = new JSONArray();
            for (int i = 0; i < arr.length(); i++) {
                JSONObject obj = arr.getJSONObject(i);
                if (!projectId.equals(obj.optString("id"))) {
                    updated.put(obj);
                }
            }
            prefs.edit().putString("flow_saved_projects", updated.toString()).apply();
            renderSavedProjects();
            Toast.makeText(this, "Project removed from local list", Toast.LENGTH_SHORT).show();
        } catch (Exception e) {
            Log.e(TAG, "Delete project error: ", e);
        }
    }

    private void confirmDeleteProject(String projectId, String projectName) {
        new AlertDialog.Builder(this)
                .setTitle("Delete Project")
                .setMessage("Are you sure you want to remove \"" + projectName + "\" from your saved projects?")
                .setPositiveButton("Delete", (dialog, which) -> {
                    deleteSavedProject(projectId);
                })
                .setNegativeButton("Cancel", null)
                .show();
    }

    public void renderSavedProjects() {
        if (containerProjectsList == null) return;
        containerProjectsList.removeAllViews();

        String json = prefs.getString("flow_saved_projects", "[]");
        try {
            JSONArray arr = new JSONArray(json);
            if (arr.length() == 0) {
                if (cardNoProjects != null) cardNoProjects.setVisibility(View.VISIBLE);
                if (containerProjectsList != null) containerProjectsList.setVisibility(View.GONE);
                if (txtProjectsCount != null) txtProjectsCount.setText("0 Projects");
                return;
            }

            if (cardNoProjects != null) cardNoProjects.setVisibility(View.GONE);
            if (containerProjectsList != null) containerProjectsList.setVisibility(View.VISIBLE);
            if (txtProjectsCount != null) txtProjectsCount.setText(arr.length() + (arr.length() == 1 ? " Project" : " Projects"));

            int dp8 = dpToPx(8);
            int dp10 = dpToPx(10);
            int dp12 = dpToPx(12);
            int dp14 = dpToPx(14);
            int dp16 = dpToPx(16);
            int dp4 = dpToPx(4);

            java.text.SimpleDateFormat sdf = new java.text.SimpleDateFormat("MMM dd, yyyy  •  hh:mm a", java.util.Locale.getDefault());

            for (int i = 0; i < arr.length(); i++) {
                JSONObject proj = arr.getJSONObject(i);
                String pid = proj.optString("id");
                String pname = proj.optString("name", "Untitled Project");
                String purl = proj.optString("url");
                long time = proj.optLong("updatedAt", System.currentTimeMillis());

                LinearLayout card = new LinearLayout(this);
                card.setOrientation(LinearLayout.HORIZONTAL);
                card.setGravity(Gravity.CENTER_VERTICAL);
                card.setBackgroundResource(R.drawable.bg_card);
                card.setPadding(dp16, dp14, dp16, dp14);

                LinearLayout.LayoutParams cardParams = new LinearLayout.LayoutParams(
                        LinearLayout.LayoutParams.MATCH_PARENT,
                        LinearLayout.LayoutParams.WRAP_CONTENT
                );
                cardParams.setMargins(0, 0, 0, dp10);
                card.setLayoutParams(cardParams);
                card.setClickable(true);
                card.setFocusable(true);

                // Initial icon box
                FrameLayout iconFrame = new FrameLayout(this);
                LinearLayout.LayoutParams iconFrameParams = new LinearLayout.LayoutParams(dpToPx(42), dpToPx(42));
                iconFrame.setLayoutParams(iconFrameParams);
                GradientDrawable iconBg = new GradientDrawable();
                iconBg.setCornerRadius(dpToPx(10));
                iconBg.setColor(Color.parseColor("#EEF2FF"));
                iconFrame.setBackground(iconBg);

                TextView iconTxt = new TextView(this);
                iconTxt.setLayoutParams(new FrameLayout.LayoutParams(FrameLayout.LayoutParams.MATCH_PARENT, FrameLayout.LayoutParams.MATCH_PARENT));
                iconTxt.setGravity(Gravity.CENTER);
                String initial = pname.isEmpty() ? "P" : pname.substring(0, 1).toUpperCase();
                iconTxt.setText(initial);
                iconTxt.setTextSize(TypedValue.COMPLEX_UNIT_SP, 17);
                iconTxt.setTextColor(Color.parseColor("#4F46E5"));
                iconTxt.setTypeface(null, android.graphics.Typeface.BOLD);
                iconFrame.addView(iconTxt);

                // Text column (Name & Date)
                LinearLayout textCol = new LinearLayout(this);
                textCol.setOrientation(LinearLayout.VERTICAL);
                LinearLayout.LayoutParams textColParams = new LinearLayout.LayoutParams(
                        0, LinearLayout.LayoutParams.WRAP_CONTENT, 1.0f
                );
                textColParams.setMarginStart(dp14);
                textCol.setLayoutParams(textColParams);

                TextView title = new TextView(this);
                title.setText(pname);
                title.setTextSize(TypedValue.COMPLEX_UNIT_SP, 15);
                title.setTextColor(getResources().getColor(R.color.text_primary, getTheme()));
                title.setTypeface(null, android.graphics.Typeface.BOLD);
                title.setMaxLines(1);
                title.setEllipsize(android.text.TextUtils.TruncateAt.END);

                TextView subtitle = new TextView(this);
                subtitle.setText(sdf.format(new java.util.Date(time)));
                subtitle.setTextSize(TypedValue.COMPLEX_UNIT_SP, 12);
                subtitle.setTextColor(getResources().getColor(R.color.text_muted, getTheme()));
                subtitle.setPadding(0, dp4, 0, 0);

                textCol.addView(title);
                textCol.addView(subtitle);

                // Open button badge
                TextView btnOpen = new TextView(this);
                btnOpen.setText("Open");
                btnOpen.setTextSize(TypedValue.COMPLEX_UNIT_SP, 12);
                btnOpen.setTextColor(Color.parseColor("#4F46E5"));
                btnOpen.setPadding(dp12, dp4 + 2, dp12, dp4 + 2);
                GradientDrawable openBg = new GradientDrawable();
                openBg.setColor(Color.parseColor("#EEF2FF"));
                openBg.setCornerRadius(dpToPx(6));
                btnOpen.setBackground(openBg);
                btnOpen.setTypeface(null, android.graphics.Typeface.BOLD);

                // Delete button
                TextView btnDelete = new TextView(this);
                btnDelete.setText("✕");
                btnDelete.setTextSize(TypedValue.COMPLEX_UNIT_SP, 14);
                btnDelete.setTextColor(Color.parseColor("#9CA3AF"));
                btnDelete.setPadding(dp10, dp4, dp4, dp4);
                btnDelete.setOnClickListener(v -> confirmDeleteProject(pid, pname));

                card.addView(iconFrame);
                card.addView(textCol);
                card.addView(btnOpen);
                card.addView(btnDelete);

                card.setOnClickListener(v -> openSavedProject(pname, purl));

                containerProjectsList.addView(card);
            }
        } catch (Exception e) {
            Log.e(TAG, "Render saved projects error: ", e);
        }
    }

    private void openSavedProject(String projectName, String projectUrl) {
        int savedPlatformId = prefs.getInt("flow_platform_id", 3);
        int savedAccountId = prefs.getInt("flow_account_id", 0);

        txtActiveAccountName.setText(projectName);
        showScreen("WEBVIEW");

        String cachedCookies = prefs.getString("cached_flow_cookies", "");
        if (!cachedCookies.isEmpty()) {
            try {
                CookieInjector.injectCookies(this, mWebView, new JSONArray(cachedCookies), projectUrl);
            } catch (Exception ignored) {}
        }

        mWebView.loadUrl(projectUrl);
        fetchCookiesInBackground(savedPlatformId, savedAccountId, projectUrl);
    }

    public void refreshDashboardData() {
        renderSavedProjects();

        if (accountsProgress != null) {
            accountsProgress.setVisibility(View.VISIBLE);
        }

        loadAssignedAccounts();
    }

    /**
     * Configures the Google Flow browsing WebView with:
     * 1. CSS & JS injection to hide user avatar / account button (same as chrome extension)
     * 2. CSS & JS injection to hide projects on home page (same as chrome extension)
     * 3. Anti-logout protection (blocks Google Sign-out navigation and click events)
     * 4. Media upload and download capabilities
     */
    public void showActionLoader(String msg) {
        runOnUiThread(() -> {
            if (txtActionLoaderMsg != null && cardActionLoader != null) {
                txtActionLoaderMsg.setText(msg);
                cardActionLoader.setAlpha(0f);
                cardActionLoader.setVisibility(View.VISIBLE);
                cardActionLoader.animate().alpha(1f).setDuration(200).start();
            }
        });
    }

    public void hideActionLoader() {
        runOnUiThread(() -> {
            if (cardActionLoader != null && cardActionLoader.getVisibility() == View.VISIBLE) {
                cardActionLoader.animate().alpha(0f).setDuration(200).withEndAction(() -> {
                    cardActionLoader.setVisibility(View.GONE);
                    cardActionLoader.setAlpha(1f);
                }).start();
            }
        });
    }

    /**
     * Configures the Google Flow browsing WebView with:
     * 1. Animated fullscreen and action loaders for smooth user feedback
     * 2. CSS & JS injection to hide user avatar / account button (same as chrome extension)
     * 3. CSS & JS injection to hide shared projects without breaking Next.js hydration or New Project
     * 4. Anti-logout protection (blocks Google Sign-out navigation and click events)
     * 5. Media upload and download capabilities
     */
    private void setupFlowWebView() {
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
        webSettings.setJavaScriptCanOpenWindowsAutomatically(true);
        webSettings.setSupportMultipleWindows(false);

        CookieManager cookieManager = CookieManager.getInstance();
        cookieManager.setAcceptCookie(true);
        cookieManager.setAcceptThirdPartyCookies(mWebView, true);

        String defaultUA = webSettings.getUserAgentString();
        webSettings.setUserAgentString(defaultUA.replace("Android", "Android 14").replace("Mobile", "Mobile"));

        mWebView.addJavascriptInterface(new ApiBridge(), "AndroidBridge");

        mWebView.setWebViewClient(new WebViewClient() {
            @Override
            public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                String url = request.getUrl().toString().toLowerCase();

                // BLOCK GOOGLE LOGOUT / SIGN OUT
                if (url.contains("signout") || url.contains("logout") ||
                        url.contains("sign_out") || url.contains("signoutoptions") ||
                        url.contains("accounts.google.com/logout") ||
                        url.contains("labs.google/fx/api/auth/signout")) {
                    Log.w(TAG, "Blocked logout attempt: " + url);
                    Toast.makeText(MainActivity.this, "Sign out from Google Flow is disabled.", Toast.LENGTH_SHORT).show();
                    return true;
                }

                // Only intercept direct file download navigations (e.g. standalone files)
                if (url.endsWith(".zip") || url.endsWith(".pdf") || url.endsWith(".tar") || url.endsWith(".apk")) {
                    String fn = "download_" + System.currentTimeMillis();
                    if (url.contains("/")) {
                        String sub = url.substring(url.lastIndexOf("/") + 1);
                        if (sub.contains("?")) sub = sub.substring(0, sub.indexOf("?"));
                        if (!sub.isEmpty()) fn = sub;
                    }
                    showActionLoader("Downloading " + fn + "...");
                    downloadUrlDirectly(request.getUrl().toString(), fn);
                    return true;
                }
                return false;
            }

            @Override
            public void onPageStarted(WebView view, String url, android.graphics.Bitmap favicon) {
                super.onPageStarted(view, url, favicon);
                if (layoutWebviewLoader != null && layoutWebview.getVisibility() == View.VISIBLE) {
                    layoutWebviewLoader.setVisibility(View.VISIBLE);
                    layoutWebviewLoader.setAlpha(1f);
                    loaderProgressBar.setProgress(15);
                    txtLoaderPercent.setText("15%");
                    txtLoaderSubtitle.setText("Connecting to Google Flow...");
                }
                hideActionLoader();
            }

            @Override
            public void onPageFinished(WebView view, String url) {
                super.onPageFinished(view, url);
                webviewProgress.setVisibility(View.GONE);
                hideActionLoader();
                // Inject extension guards after page has fully hydrated (500ms delay to prevent React hydration error 418)
                view.postDelayed(() -> injectExtensionGuards(view), 500);
            }
        });

        mWebView.setWebChromeClient(new WebChromeClient() {
            @Override
            public void onProgressChanged(WebView view, int newProgress) {
                if (newProgress < 100) {
                    webviewProgress.setVisibility(View.VISIBLE);
                    if (layoutWebviewLoader != null && layoutWebview.getVisibility() == View.VISIBLE) {
                        layoutWebviewLoader.setVisibility(View.VISIBLE);
                        loaderProgressBar.setProgress(newProgress);
                        txtLoaderPercent.setText(newProgress + "%");
                        if (newProgress < 35) {
                            txtLoaderSubtitle.setText("Connecting to Google Flow...");
                        } else if (newProgress < 75) {
                            txtLoaderSubtitle.setText("Loading AI creative studio...");
                        } else {
                            txtLoaderSubtitle.setText("Preparing workspace...");
                        }
                    }
                } else {
                    webviewProgress.setVisibility(View.GONE);
                    if (layoutWebviewLoader != null && layoutWebviewLoader.getVisibility() == View.VISIBLE) {
                        loaderProgressBar.setProgress(100);
                        txtLoaderPercent.setText("100%");
                        layoutWebviewLoader.animate()
                                .alpha(0f)
                                .setDuration(300)
                                .withEndAction(() -> {
                                    layoutWebviewLoader.setVisibility(View.GONE);
                                    layoutWebviewLoader.setAlpha(1f);
                                })
                                .start();
                    }
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
                String filename = "flow_" + System.currentTimeMillis();
                if (url != null && url.contains("/")) {
                    String sub = url.substring(url.lastIndexOf("/") + 1);
                    if (sub.contains("?")) sub = sub.substring(0, sub.indexOf("?"));
                    if (!sub.isEmpty()) filename = URLDecoder.decode(sub, "UTF-8");
                }
                boolean isVid = (mimetype != null && mimetype.contains("video")) || (url != null && url.contains(".mp4"));
                if (!filename.contains(".")) {
                    filename += isVid ? ".mp4" : ".png";
                }
                showActionLoader("Downloading " + filename + "...");
                downloadUrlDirectly(url, filename);
            } catch (Exception e) {
                Toast.makeText(getApplicationContext(), "Download error: " + e.getMessage(), Toast.LENGTH_LONG).show();
            }
        });
    }

    /**
     * Saves any file (image, video, document, zip) directly into device Downloads/Gallery via MediaStore
     */
    public void saveMediaToStorage(String base64Data, String filename, String mimeType) {
        new Thread(() -> {
            try {
                byte[] bytes = android.util.Base64.decode(base64Data, android.util.Base64.DEFAULT);
                boolean isVideo = (mimeType != null && (mimeType.contains("video") || mimeType.contains("mp4"))) || filename.toLowerCase().endsWith(".mp4");
                boolean isImage = (mimeType != null && mimeType.contains("image")) || filename.toLowerCase().endsWith(".png") || filename.toLowerCase().endsWith(".jpg") || filename.toLowerCase().endsWith(".jpeg") || filename.toLowerCase().endsWith(".webp");
                String finalMime = isVideo ? "video/mp4" : (isImage ? (mimeType != null && !mimeType.isEmpty() ? mimeType : "image/jpeg") : (mimeType != null && !mimeType.isEmpty() ? mimeType : "application/octet-stream"));

                ContentResolver resolver = getContentResolver();
                ContentValues contentValues = new ContentValues();
                contentValues.put(MediaStore.MediaColumns.DISPLAY_NAME, filename);
                contentValues.put(MediaStore.MediaColumns.MIME_TYPE, finalMime);

                Uri contentUri;
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
                    if (isVideo) {
                        contentValues.put(MediaStore.MediaColumns.RELATIVE_PATH, Environment.DIRECTORY_MOVIES);
                        contentValues.put(MediaStore.MediaColumns.IS_PENDING, 1);
                        contentUri = resolver.insert(MediaStore.Video.Media.EXTERNAL_CONTENT_URI, contentValues);
                    } else if (isImage) {
                        contentValues.put(MediaStore.MediaColumns.RELATIVE_PATH, Environment.DIRECTORY_PICTURES);
                        contentValues.put(MediaStore.MediaColumns.IS_PENDING, 1);
                        contentUri = resolver.insert(MediaStore.Images.Media.EXTERNAL_CONTENT_URI, contentValues);
                    } else {
                        contentValues.put(MediaStore.MediaColumns.RELATIVE_PATH, Environment.DIRECTORY_DOWNLOADS);
                        contentValues.put(MediaStore.MediaColumns.IS_PENDING, 1);
                        contentUri = resolver.insert(MediaStore.Downloads.EXTERNAL_CONTENT_URI, contentValues);
                    }
                } else {
                    File dir = Environment.getExternalStoragePublicDirectory(isVideo ? Environment.DIRECTORY_MOVIES : (isImage ? Environment.DIRECTORY_PICTURES : Environment.DIRECTORY_DOWNLOADS));
                    if (!dir.exists()) dir.mkdirs();
                    File file = new File(dir, filename);
                    contentValues.put(MediaStore.MediaColumns.DATA, file.getAbsolutePath());
                    contentUri = resolver.insert(isVideo ? MediaStore.Video.Media.EXTERNAL_CONTENT_URI : (isImage ? MediaStore.Images.Media.EXTERNAL_CONTENT_URI : MediaStore.Files.getContentUri("external")), contentValues);
                }

                if (contentUri != null) {
                    try (OutputStream out = resolver.openOutputStream(contentUri)) {
                        if (out != null) {
                            out.write(bytes);
                            out.flush();
                        }
                    }

                    if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
                        contentValues.clear();
                        contentValues.put(MediaStore.MediaColumns.IS_PENDING, 0);
                        resolver.update(contentUri, contentValues, null, null);
                    }

                    runOnUiThread(() -> {
                        hideActionLoader();
                        Toast.makeText(MainActivity.this, "Saved: " + filename, Toast.LENGTH_LONG).show();
                    });
                }
            } catch (Exception e) {
                Log.e(TAG, "Save media error: ", e);
                runOnUiThread(() -> {
                    hideActionLoader();
                    Toast.makeText(MainActivity.this, "Save error: " + e.getMessage(), Toast.LENGTH_LONG).show();
                });
            }
        }).start();
    }

    public void downloadUrlDirectly(String url, String filename) {
        new Thread(() -> {
            try {
                String cookie = CookieManager.getInstance().getCookie(url);
                java.net.URL u = new java.net.URL(url);
                java.net.HttpURLConnection conn = (java.net.HttpURLConnection) u.openConnection();
                conn.setInstanceFollowRedirects(true);
                if (cookie != null) conn.setRequestProperty("Cookie", cookie);
                conn.setRequestProperty("User-Agent", mWebView.getSettings().getUserAgentString());
                conn.connect();

                String mime = conn.getContentType();
                java.io.InputStream in = conn.getInputStream();
                java.io.ByteArrayOutputStream buffer = new java.io.ByteArrayOutputStream();
                byte[] data = new byte[8192];
                int nRead;
                while ((nRead = in.read(data, 0, data.length)) != -1) {
                    buffer.write(data, 0, nRead);
                }
                buffer.flush();
                byte[] fileBytes = buffer.toByteArray();
                String base64 = android.util.Base64.encodeToString(fileBytes, android.util.Base64.NO_WRAP);
                saveMediaToStorage(base64, filename, mime);
            } catch (Exception e) {
                Log.e(TAG, "Download URL error: ", e);
                runOnUiThread(() -> {
                    hideActionLoader();
                    Toast.makeText(MainActivity.this, "Download failed: " + e.getMessage(), Toast.LENGTH_LONG).show();
                });
            }
        }).start();
    }

    /**
     * Injects CSS and JavaScript matching the chrome extension into Google Flow:
     * 1. Hides Google account avatar circle & account button
     * 2. Hides shared project cards on the home page without hiding the scroller or creation panel
     * 3. Guarantees "+ New Project" button and its creation panel/drawer work 100%
     * 4. Disables and blocks any Sign-out / Logout clicks
     * 5. Triggers custom in-app action loader when clicking New Project or Generate
     * 6. Universal downloader: intercepts ANY download (blob, data, anchor, 1K/2K/4K, any file)
     */
    private void injectExtensionGuards(WebView view) {
        String js = "(function() {" +
                "  /* 1. UNIVERSAL MEDIA & FILE DOWNLOAD HELPER */" +
                "  var _downloadLock = false;" +
                "  function handleMediaDownload(url, filename) {" +
                "    if (!url || typeof url !== 'string') return;" +
                "    if (_downloadLock) return;" +
                "    _downloadLock = true;" +
                "    setTimeout(function() { _downloadLock = false; }, 3000);" +
                "    if (window.AndroidBridge && window.AndroidBridge.onActionStarted) {" +
                "      window.AndroidBridge.onActionStarted('Downloading file...');" +
                "    }" +
                "    if (url.startsWith('data:')) {" +
                "      var parts = url.split(',');" +
                "      var mime = (parts[0].match(/:(.*?);/) || [])[1] || 'application/octet-stream';" +
                "      if (window.AndroidBridge && window.AndroidBridge.saveMediaBase64) {" +
                "        window.AndroidBridge.saveMediaBase64(parts[1], filename || ('file_' + Date.now()), mime);" +
                "      }" +
                "      return;" +
                "    }" +
                "    fetch(url)" +
                "      .then(function(res) {" +
                "        var ct = res.headers.get('content-type') || '';" +
                "        return res.blob().then(function(b) { return { blob: b, mime: ct || b.type }; });" +
                "      })" +
                "      .then(function(resData) {" +
                "        var blob = resData.blob;" +
                "        var mime = resData.mime || blob.type || 'application/octet-stream';" +
                "        var reader = new FileReader();" +
                "        reader.onloadend = function() {" +
                "          var base64 = (reader.result || '').split(',')[1];" +
                "          if (base64 && window.AndroidBridge && window.AndroidBridge.saveMediaBase64) {" +
                "            var isVid = mime.includes('video') || (filename && filename.endsWith('.mp4'));" +
                "            var isImg = mime.includes('image') || (filename && (filename.endsWith('.jpg') || filename.endsWith('.png') || filename.endsWith('.jpeg')));" +
                "            var ext = isVid ? '.mp4' : (mime.includes('png') ? '.png' : (isImg ? '.jpg' : ''));" +
                "            var finalName = filename || ('download_' + Date.now() + ext);" +
                "            if (ext && !finalName.endsWith(ext)) finalName = finalName.replace(/\\.[a-zA-Z0-9]+$/, '') + ext;" +
                "            window.AndroidBridge.saveMediaBase64(base64, finalName, mime);" +
                "          }" +
                "        };" +
                "        reader.readAsDataURL(blob);" +
                "      })" +
                "      .catch(function() {" +
                "        if (window.AndroidBridge && window.AndroidBridge.downloadFromUrl) {" +
                "          window.AndroidBridge.downloadFromUrl(url, filename || ('download_' + Date.now()));" +
                "        }" +
                "      });" +
                "  }" +
                "" +
                "  /* 2. UNIVERSAL DOWNLOAD & WINDOW.OPEN INTERCEPTORS */" +
                "  try {" +
                "    var _blobMap = new Map();" +
                "    if (window.URL && window.URL.createObjectURL) {" +
                "      var _origCreate = window.URL.createObjectURL;" +
                "      window.URL.createObjectURL = function(obj) {" +
                "        var u = _origCreate.apply(this, arguments);" +
                "        if (obj instanceof Blob) _blobMap.set(u, obj);" +
                "        return u;" +
                "      };" +
                "    }" +
                "" +
                "    var _origAnchorClick = HTMLAnchorElement.prototype.click;" +
                "    HTMLAnchorElement.prototype.click = function() {" +
                "      var href = this.href || '';" +
                "      var dl = this.download || this.getAttribute('download');" +
                "      var hasDl = this.hasAttribute('download') || (dl !== null && dl !== undefined && dl !== '');" +
                "      var isBlobOrData = href.startsWith('blob:') || href.startsWith('data:');" +
                "      var isFile = /\\.(mp4|png|jpg|jpeg|webp|gif|webm|mov|wav|mp3|zip|pdf)(\\?|$)/i.test(href) || href.includes('getMediaUrlRedirect');" +
                "" +
                "      if (hasDl || isBlobOrData || isFile) {" +
                "        var fn = dl || ('file_' + Date.now());" +
                "        if (href.startsWith('blob:')) {" +
                "          var cached = _blobMap.get(href);" +
                "          if (cached) {" +
                "            var r = new FileReader();" +
                "            r.onloadend = function() {" +
                "              var b64 = (r.result || '').split(',')[1];" +
                "              if (b64 && window.AndroidBridge && window.AndroidBridge.saveMediaBase64) {" +
                "                window.AndroidBridge.saveMediaBase64(b64, fn, cached.type || 'application/octet-stream');" +
                "              }" +
                "            };" +
                "            r.readAsDataURL(cached);" +
                "            return;" +
                "          }" +
                "        }" +
                "        handleMediaDownload(href, fn);" +
                "        return;" +
                "      }" +
                "      return _origAnchorClick.apply(this, arguments);" +
                "    };" +
                "" +
                "    var _proxyWin = {" +
                "      location: {" +
                "        set href(val) {" +
                "          if (val && (val.includes('getMediaUrlRedirect') || val.includes('.mp4') || val.includes('.png') || val.includes('.jpg') || val.startsWith('blob:') || val.startsWith('data:'))) {" +
                "            handleMediaDownload(val, 'flow_' + Date.now());" +
                "            return;" +
                "          }" +
                "          if (val) window.location.href = val;" +
                "        }," +
                "        get href() { return window.location.href; }," +
                "        replace: function(val) {" +
                "          if (val && (val.includes('getMediaUrlRedirect') || val.includes('.mp4') || val.includes('.png') || val.includes('.jpg') || val.startsWith('blob:') || val.startsWith('data:'))) {" +
                "            handleMediaDownload(val, 'flow_' + Date.now());" +
                "            return;" +
                "          }" +
                "          if (val) window.location.replace(val);" +
                "        }," +
                "        assign: function(val) {" +
                "          if (val && (val.includes('getMediaUrlRedirect') || val.includes('.mp4') || val.includes('.png') || val.includes('.jpg') || val.startsWith('blob:') || val.startsWith('data:'))) {" +
                "            handleMediaDownload(val, 'flow_' + Date.now());" +
                "            return;" +
                "          }" +
                "          if (val) window.location.assign(val);" +
                "        }" +
                "      }," +
                "      focus: function() {}," +
                "      close: function() {}," +
                "      document: document" +
                "    };" +
                "    window.open = function(url) {" +
                "      if (url && typeof url === 'string') {" +
                "        if (url.includes('getMediaUrlRedirect') || url.includes('.mp4') || url.includes('.png') || url.includes('.jpg') || url.startsWith('blob:') || url.startsWith('data:')) {" +
                "          handleMediaDownload(url, 'flow_' + Date.now());" +
                "          return _proxyWin;" +
                "        }" +
                "        window.location.href = url;" +
                "      }" +
                "      return _proxyWin;" +
                "    };" +
                "  } catch(_) {}" +
                "" +
                "  /* 3. TARGETED CSS (HIDES GOOGLE AVATAR & SHARED PROJECTS ONLY) */" +
                "  if (!document.getElementById('__wemate_guard_style__')) {" +
                "    var style = document.createElement('style');" +
                "    style.id = '__wemate_guard_style__';" +
                "    style.textContent = `" +
                "      /* HIDE GOOGLE ACCOUNT AVATAR IMAGE INSIDE ULTRA BUTTON */" +
                "      button img[src*='googleusercontent.com']," +
                "      header button img[src*='googleusercontent.com']," +
                "      [aria-label*='Google Account' i]," +
                "      [aria-label*='Account' i]," +
                "      a[href*='accounts.google']," +
                "      a[href*='signout'] {" +
                "        display: none !important;" +
                "        visibility: hidden !important;" +
                "        pointer-events: none !important;" +
                "        width: 0 !important;" +
                "        height: 0 !important;" +
                "      }" +
                "      /* HIDE SHARED PROJECT CARDS ON HOME PAGE WITHOUT BREAKING MEASUREMENTS */" +
                "      html[data-flow-home='1'] [data-testid='virtuoso-item-list']," +
                "      html[data-flow-home='1'] div:has(> a[href*='/project/']) {" +
                "        visibility: hidden !important;" +
                "        opacity: 0 !important;" +
                "        pointer-events: none !important;" +
                "      }" +
                "      /* HIDE SECTION HEADERS ON HOME PAGE */" +
                "      html[data-flow-home='1'] h1," +
                "      html[data-flow-home='1'] h2," +
                "      html[data-flow-home='1'] h3," +
                "      html[data-flow-home='1'] h4," +
                "      html[data-flow-home='1'] h5 {" +
                "        display: none !important;" +
                "      }" +
                "      /* ALWAYS PRESERVE NEW PROJECT BUTTON & CREATION DIALOGS */" +
                "      button," +
                "      [role='button']," +
                "      [role='dialog']," +
                "      [role='tabpanel']," +
                "      [role='tab']," +
                "      input," +
                "      textarea {" +
                "        opacity: 1 !important;" +
                "        visibility: visible !important;" +
                "        pointer-events: auto !important;" +
                "      }" +
                "    `;" +
                "    (document.head || document.documentElement).appendChild(style);" +
                "  }" +
                "" +
                "  if (window.__wemate_guard_injected__) return;" +
                "  window.__wemate_guard_injected__ = true;" +
                "" +
                "  /* 4. LIGHTWEIGHT PROJECT CARD CONTROLLER & PROJECT AUTO-SAVE */" +
                "  function detectAndSaveProject() {" +
                "    var path = location.pathname;" +
                "    var m = path.match(/\\/project\\/([a-zA-Z0-9_-]+)/);" +
                "    if (m && m[1]) {" +
                "      var pid = m[1];" +
                "      var purl = 'https://labs.google/fx/tools/flow/project/' + pid;" +
                "" +
                "      var name = '';" +
                "      var titleInput = document.querySelector('header input, input[placeholder*=\"Untitled\" i], [data-testid=\"project-title\"]');" +
                "      if (titleInput && titleInput.value) {" +
                "        name = titleInput.value.trim();" +
                "      }" +
                "      if (!name) {" +
                "        var h = document.querySelector('header h1, header h2, [data-testid=\"project-name\"]');" +
                "        if (h && h.textContent) name = h.textContent.trim();" +
                "      }" +
                "      if (!name) {" +
                "        name = (document.title || '').replace(/^Google\\s*Flow\\s*[-–—]\\s*/i, '').trim();" +
                "      }" +
                "      if (!name || name.toLowerCase() === 'google flow') {" +
                "        name = 'Untitled Project';" +
                "      }" +
                "" +
                "      if (window.AndroidBridge && window.AndroidBridge.saveUserProject) {" +
                "        window.AndroidBridge.saveUserProject(pid, name, purl);" +
                "      }" +
                "    }" +
                "  }" +
                "" +
                "  document.addEventListener('input', function(e) {" +
                "    if (location.pathname.indexOf('/project/') !== -1) {" +
                "      detectAndSaveProject();" +
                "    }" +
                "  }, true);" +
                "" +
                "  function applyGuards() {" +
                "    var isHome = location.pathname.indexOf('/project/') === -1;" +
                "    if (isHome) {" +
                "      document.documentElement.setAttribute('data-flow-home', '1');" +
                "      document.querySelectorAll('div:has(> a[href*=\"/project/\"]), [data-testid=\"virtuoso-item-list\"]').forEach(function(el) {" +
                "        el.style.setProperty('visibility', 'hidden', 'important');" +
                "        el.style.setProperty('opacity', '0', 'important');" +
                "        el.style.setProperty('pointer-events', 'none', 'important');" +
                "      });" +
                "    } else {" +
                "      document.documentElement.removeAttribute('data-flow-home');" +
                "      detectAndSaveProject();" +
                "    }" +
                "" +
                "    /* Disable clicks on account menu button in header */" +
                "    document.querySelectorAll('button').forEach(function(b) {" +
                "      if (b.querySelector('img[src*=\"googleusercontent.com\"]') ||" +
                "          (b.getAttribute('aria-label') || '').toLowerCase().includes('google account')) {" +
                "        b.style.pointerEvents = 'none';" +
                "      }" +
                "    });" +
                "  }" +
                "" +
                "  applyGuards();" +
                "" +
                "  /* 5. ACTION LOADER, DOWNLOAD CLICK HANDLER & GOOGLE LOGOUT BLOCKER */" +
                "  document.addEventListener('click', function(e) {" +
                "    var el = e.target;" +
                "    for (var i = 0; i < 6 && el; i++) {" +
                "      var txt = (el.textContent || '').trim().toLowerCase();" +
                "      var aria = (el.getAttribute('aria-label') || '').toLowerCase();" +
                "      var href = (el.getAttribute('href') || '').toLowerCase();" +
                "" +
                "      /* Block Google Sign-Out clicks */" +
                "      if (txt.includes('sign out') || txt.includes('signout') ||" +
                "          txt.includes('log out') || txt.includes('logout') ||" +
                "          aria.includes('sign out') || href.includes('signout') || href.includes('logout')) {" +
                "        e.preventDefault();" +
                "        e.stopPropagation();" +
                "        e.stopImmediatePropagation();" +
                "        return false;" +
                "      }" +
                "" +
                "      /* Only trigger on actual quality resolution selections (1K, 2K, 4K, Original size, Upscaled) */" +
                "      var clean = txt.replace(/\\s+/g, ' ');" +
                "      var isResolutionAction = (el.getAttribute('role') === 'menuitem' || el.tagName === 'BUTTON') &&" +
                "          (clean === '1k' || clean === '2k' || clean === '4k' ||" +
                "           clean.includes('1k original') || clean.includes('original size') ||" +
                "           clean.includes('2k upscaled') || clean.includes('4k upscaled') ||" +
                "           clean.startsWith('1k') || clean.startsWith('2k') || clean.startsWith('4k'));" +
                "" +
                "      if (isResolutionAction) {" +
                "        if (_downloadLock) break;" +
                "        _downloadLock = true;" +
                "        setTimeout(function() { _downloadLock = false; }, 3000);" +
                "" +
                "        if (window.AndroidBridge && window.AndroidBridge.onActionStarted) {" +
                "          window.AndroidBridge.onActionStarted('Downloading media...');" +
                "        }" +
                "        setTimeout(function() {" +
                "          var media = document.querySelector('img[src*=\"getMediaUrlRedirect\"], video[src*=\"getMediaUrlRedirect\"], [role=\"dialog\"] img, [role=\"dialog\"] video');" +
                "          if (!media) {" +
                "            var imgs = Array.from(document.querySelectorAll('img[src*=\"getMediaUrlRedirect\"], video[src*=\"getMediaUrlRedirect\"], video, img'));" +
                "            imgs = imgs.filter(function(m) {" +
                "              var r = m.getBoundingClientRect();" +
                "              return r.width > 100 && r.height > 100 && m.src && !m.src.includes('googleusercontent.com/a/');" +
                "            });" +
                "            if (imgs.length > 0) media = imgs[imgs.length - 1];" +
                "          }" +
                "          if (media && media.src) {" +
                "            var isVid = media.tagName === 'VIDEO' || media.src.includes('.mp4');" +
                "            var ext = isVid ? '.mp4' : '.jpg';" +
                "            var prefix = clean.includes('4k') ? 'flow_4k_' : (clean.includes('2k') ? 'flow_2k_' : (isVid ? 'flow_video_' : 'flow_image_'));" +
                "            var fn = prefix + Date.now() + ext;" +
                "            handleMediaDownload(media.src, fn);" +
                "          }" +
                "        }, 120);" +
                "        break;" +
                "      }" +
                "" +
                "      /* Trigger in-app action loader for New Project click */" +
                "      if (txt.includes('new project') || txt.includes('create new') || aria.includes('new project')) {" +
                "        if (window.AndroidBridge && window.AndroidBridge.onActionStarted) {" +
                "          window.AndroidBridge.onActionStarted('Creating new project...');" +
                "        }" +
                "        setTimeout(function() {" +
                "          if (window.AndroidBridge && window.AndroidBridge.onActionFinished) {" +
                "            window.AndroidBridge.onActionFinished();" +
                "          }" +
                "        }, 4500);" +
                "        break;" +
                "      }" +
                "" +
                "      /* Trigger in-app action loader for Generate video click */" +
                "      if (txt === 'generate' || aria.includes('generate')) {" +
                "        if (window.AndroidBridge && window.AndroidBridge.onActionStarted) {" +
                "          window.AndroidBridge.onActionStarted('Generating video...');" +
                "        }" +
                "        setTimeout(function() {" +
                "          if (window.AndroidBridge && window.AndroidBridge.onActionFinished) {" +
                "            window.AndroidBridge.onActionFinished();" +
                "          }" +
                "        }, 6000);" +
                "        break;" +
                "      }" +
                "" +
                "      el = el.parentElement;" +
                "    }" +
                "  }, true);" +
                "" +
                "  /* 6. MUTATION OBSERVER WITH 400ms DEBOUNCE */" +
                "  var _t = null;" +
                "  var observer = new MutationObserver(function() {" +
                "    if (_t) return;" +
                "    _t = setTimeout(function() {" +
                "      _t = null;" +
                "      applyGuards();" +
                "    }, 400);" +
                "  });" +
                "  observer.observe(document.documentElement, { childList: true, subtree: true });" +
                "})();";

        view.post(() -> view.evaluateJavascript(js, null));
    }

    /**
     * JavaScript Bridge Interface connected to the dedicated API WebView
     */
    public class ApiBridge {

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
                        String email = userObj != null ? userObj.optString("email", user) : user;
                        String validity = userObj != null ? userObj.optString("validity_text", "") : "";
                        boolean isExpired = userObj != null && userObj.optBoolean("is_expired", false);

                        prefs.edit()
                                .putString("fullname", fullname)
                                .putString("email", email)
                                .putString("validity_text", validity)
                                .putBoolean("is_expired", isExpired)
                                .putBoolean("is_logged_in", true)
                                .apply();

                        updateUserInfo(fullname, email, validity, isExpired);
                        showScreen("ACCOUNTS");
                        renderCachedAccounts();
                        loadAssignedAccounts();
                    } else {
                        String msg = response.optString("message", "Login failed");
                        Toast.makeText(MainActivity.this, msg, Toast.LENGTH_LONG).show();
                        showScreen("LOGIN");
                    }
                } catch (Exception e) {
                    Toast.makeText(MainActivity.this, "Response error: " + e.getMessage(), Toast.LENGTH_LONG).show();
                    showScreen("LOGIN");
                }
            });
        }

        @JavascriptInterface
        public void onPlatformsResult(String jsonStr) {
            runOnUiThread(() -> {
                isRefreshingAccounts = false;
                if (accountsProgress != null) {
                    accountsProgress.setVisibility(View.GONE);
                }

                try {
                    JSONObject response = new JSONObject(jsonStr);
                    if (response.optBoolean("success", false)) {
                        // Cache platforms locally
                        prefs.edit().putString("cached_platforms", jsonStr).apply();
                        displayPlatformsJson(jsonStr);
                    } else {
                        String msg = response.optString("message", "");
                        if ("Unauthorized".equalsIgnoreCase(msg) || response.optInt("status") == 401 || msg.contains("Unauthenticated")) {
                            // Re-login in background
                            String savedUser = prefs.getString("saved_username", "");
                            String savedPass = prefs.getString("saved_password", "");
                            if (!savedUser.isEmpty() && !savedPass.isEmpty()) {
                                performLogin(savedUser, savedPass, true);
                            }
                        } else {
                            if (containerAccountList.getChildCount() == 0) {
                                txtNoAccounts.setText(msg.isEmpty() ? "Failed to fetch accounts" : msg);
                                txtNoAccounts.setVisibility(View.VISIBLE);
                            }
                        }
                    }
                } catch (Exception e) {
                    if (containerAccountList.getChildCount() == 0) {
                        txtNoAccounts.setText("Error parsing accounts");
                        txtNoAccounts.setVisibility(View.VISIBLE);
                    }
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
                        if (cookies != null && cookies.length() > 0) {
                            int activePlatformId = prefs.getInt("flow_platform_id", 3);
                            int activeAccountId = prefs.getInt("flow_account_id", 0);
                            String cookieKey = "cached_cookies_" + activePlatformId + "_" + activeAccountId;
                            prefs.edit().putString(cookieKey, cookies.toString())
                                        .putString("cached_flow_cookies", cookies.toString()).apply();
                            CookieInjector.injectCookies(MainActivity.this, mWebView, cookies, targetUrl);
                        }
                    } else {
                        String msg = response.optString("message", "");
                        if ("Unauthorized".equalsIgnoreCase(msg)) {
                            // Re-login in background silently so session is refreshed
                            String savedUser = prefs.getString("saved_username", "");
                            String savedPass = prefs.getString("saved_password", "");
                            if (!savedUser.isEmpty() && !savedPass.isEmpty()) {
                                performLogin(savedUser, savedPass, true);
                            }
                        }
                    }
                } catch (Exception ignored) {}
            });
        }

        @JavascriptInterface
        public void onApiError(String errorMsg) {
            runOnUiThread(() -> {
                isRefreshingAccounts = false;
                loginProgress.setVisibility(View.GONE);
                btnLogin.setEnabled(true);
                if (accountsProgress != null) {
                    accountsProgress.setVisibility(View.GONE);
                }
                webviewProgress.setVisibility(View.GONE);

                // If accounts are already displayed from cache, don't disturb the user with a toast
                if (containerAccountList.getChildCount() == 0) {
                    Toast.makeText(MainActivity.this, errorMsg, Toast.LENGTH_LONG).show();
                }

                // If currently showing webview and it's empty, fallback load Google Flow directly
                if (layoutWebview.getVisibility() == View.VISIBLE && (mWebView.getUrl() == null || mWebView.getUrl().equals("about:blank"))) {
                    mWebView.loadUrl("https://labs.google/fx/tools/flow");
                }
            });
        }

        @JavascriptInterface
        public void onActionStarted(String actionName) {
            showActionLoader(actionName);
        }

        @JavascriptInterface
        public void onActionFinished() {
            hideActionLoader();
        }

        @JavascriptInterface
        public void saveMediaBase64(String base64Data, String filename, String mimeType) {
            saveMediaToStorage(base64Data, filename, mimeType);
        }

        @JavascriptInterface
        public void downloadFromUrl(String url, String filename) {
            downloadUrlDirectly(url, filename);
        }

        @JavascriptInterface
        public void saveUserProject(String projectId, String projectName, String projectUrl) {
            runOnUiThread(() -> {
                MainActivity.this.saveUserProject(projectId, projectName, projectUrl);
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
    protected void onResume() {
        super.onResume();
        if (layoutAccounts != null && layoutAccounts.getVisibility() == View.VISIBLE) {
            refreshDashboardData();
        }
    }

    @Override
    public void onBackPressed() {
        if (drawerLayout.isDrawerOpen(GravityCompat.START)) {
            drawerLayout.closeDrawer(GravityCompat.START);
        } else if (layoutWebview.getVisibility() == View.VISIBLE) {
            if (mWebView.canGoBack()) {
                mWebView.goBack();
            } else {
                showScreen("ACCOUNTS");
                refreshDashboardData();
            }
        } else if (layoutAccounts.getVisibility() == View.VISIBLE) {
            super.onBackPressed();
        } else {
            super.onBackPressed();
        }
    }
}