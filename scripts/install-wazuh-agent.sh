#!/bin/bash
# ================================================
# Script: Install Wazuh Agent di VPS CISMART
# Jalankan di SSH: sudo bash scripts/install-wazuh-agent.sh
# ================================================

set -e

WAZUH_MANAGER_IP="127.0.0.1"   # Manager jalan di Docker host yang sama
AGENT_NAME="cismart-vps-$(hostname)"

echo "🛡️ Installing Wazuh Agent..."

# Install
curl -s https://packages.wazuh.com/key/GPG-KEY-WAZUH | gpg --dearmor -o /usr/share/keyrings/wazuh.gpg
echo "deb [signed-by=/usr/share/keyrings/wazuh.gpg] https://packages.wazuh.com/4.x/apt/ stable main" \
  > /etc/apt/sources.list.d/wazuh.list
apt-get update && apt-get install wazuh-agent -y

# Konfigurasi: arahkan ke Wazuh Manager
cat > /var/ossec/etc/ossec.conf << EOF
<ossec_config>
  <client>
    <server>
      <address>${WAZUH_MANAGER_IP}</address>
      <port>1514</port>
      <protocol>udp</protocol>
    </server>
    <auto_restart>yes</auto_restart>
    <notify_time>10</notify_time>
    <time-reconnect>60</time-reconnect>
  </client>

  <!-- Monitor Docker logs -->
  <localfile>
    <log_format>json</log_format>
    <location>/var/lib/docker/containers/*/*-json.log</location>
  </localfile>

  <!-- Monitor SSH auth -->
  <localfile>
    <log_format>syslog</log_format>
    <location>/var/log/auth.log</location>
  </localfile>

  <!-- Monitor system log -->
  <localfile>
    <log_format>syslog</log_format>
    <location>/var/log/syslog</location>
  </localfile>

  <!-- File Integrity Monitoring -->
  <syscheck>
    <frequency>3600</frequency>
    <directories check_all="yes" report_changes="yes" realtime="yes">
      /var/www/cismart-2/laravel_app/app
    </directories>
    <directories check_all="yes" realtime="yes">
      /var/www/cismart-2/laravel_app/config
    </directories>
    <directories check_all="yes" realtime="yes">
      /var/www/cismart-2/ml_service
    </directories>
    <ignore>/var/www/cismart-2/laravel_app/storage</ignore>
    <ignore>/var/www/cismart-2/laravel_app/bootstrap/cache</ignore>
  </syscheck>

  <!-- Rootcheck -->
  <rootcheck>
    <disabled>no</disabled>
  </rootcheck>
</ossec_config>
EOF

# Register agent ke Manager
WAZUH_MANAGER="${WAZUH_MANAGER_IP}" \
WAZUH_AGENT_NAME="${AGENT_NAME}" \
wazuh-agent --register-agent 2>/dev/null || true

# Start agent
systemctl daemon-reload
systemctl enable wazuh-agent
systemctl start wazuh-agent

echo "✅ Wazuh Agent installed and running!"
echo "   Agent name: ${AGENT_NAME}"
echo "   Pointing to: ${WAZUH_MANAGER_IP}:1514"
systemctl status wazuh-agent --no-pager
