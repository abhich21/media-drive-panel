<?php
/**
 * MDM Header Component
 * Top header with branding, settings, and profile - Pixel Perfect Version
 */

$user = getCurrentUser();
$userName = $user['name'] ?? 'User';
$userAvatar = $user['avatar'] ?? null;
?>

<style>
    /* Header Critical Styles */
    .mdm-header {
        position: fixed;
        top: 0;
        left: 240px;
        right: 0;
        height: 72px;
        background-color: #E6E7E2;
        z-index: 40;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 32px;
    }

    .mdm-header-brand {
        font-size: 18px;
        font-weight: 700;
        color: #000000;
    }

    .mdm-header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .mdm-settings-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #D9D9D6;
        color: #000000;
        padding: 10px 20px;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }

    .mdm-settings-btn:hover {
        background: #CFCFCC;
    }

    .mdm-settings-btn svg {
        width: 18px;
        height: 18px;
    }

    .mdm-profile-btn {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #FFFFFF;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        transition: box-shadow 0.2s;
    }

    .mdm-profile-btn:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }

    .mdm-profile-btn svg {
        width: 22px;
        height: 22px;
        color: #000000;
    }

    /* Mobile Responsive Header */
    @media (max-width: 768px) {
        .mdm-header {
            left: 0;
            padding: 0 16px;
            height: 60px;
        }

        .mdm-header-brand {
            font-size: 14px;
        }

        .mdm-settings-btn {
            padding: 8px 14px;
            font-size: 12px;
        }

        .mdm-settings-btn svg {
            width: 16px;
            height: 16px;
        }

        .mdm-profile-btn {
            width: 36px;
            height: 36px;
        }

        .mdm-profile-btn svg {
            width: 18px;
            height: 18px;
        }

        .mdm-hamburger {
            display: none;
        }
    }

    /* Hamburger menu button */
    .mdm-hamburger {
        display: none;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #000000;
        border: none;
        cursor: pointer;
        align-items: center;
        justify-content: center;
        color: #FFFFFF;
    }

    .mdm-hamburger svg {
        width: 22px;
        height: 22px;
    }

    @media (max-width: 768px) {
        .mdm-hamburger {
            display: flex;
        }
    }
</style>

<!-- Header Bar -->
<header class="mdm-header">
    <!-- Hamburger Menu (Mobile) -->
    <button class="mdm-hamburger" onclick="openSidebar()">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <!-- Center Brand -->
    <div class="mdm-header-brand">Client Branding</div>

    <!-- Right Actions -->
    <div class="mdm-header-actions">
        <!-- Settings Button -->
        <button class="mdm-settings-btn" onclick="openSettings()">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>Setting</span>
        </button>

        <!-- Profile Button -->
        <button class="mdm-profile-btn" onclick="openProfile()">
            <?php if ($userAvatar): ?>
                <img src="<?= h($userAvatar) ?>" alt="Profile"
                    style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
            <?php else: ?>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            <?php endif; ?>
        </button>

        <!-- Logout Button -->
        <button class="mdm-settings-btn" onclick="logoutUser()" style="background: #5D4B3A; color: #FFFFFF;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span>Logout</span>
        </button>
    </div>
</header>

<script>
    function openSettings() {
        console.log('Open settings');
    }

    function openProfile() {
        console.log('Open profile');
    }

    function logoutUser() {
        fetch('<?= BASE_PATH ?>/api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=logout'
        })
            .then(response => response.json())
            .then(data => {
                window.location.href = '<?= BASE_PATH ?>/login.php';
            })
            .catch(err => {
                window.location.href = '<?= BASE_PATH ?>/login.php';
            });
    }
</script>