import { Injectable } from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';

@Injectable()
export class TelegramService {
  constructor(private prisma: PrismaService) {}

  async getTelegramConfig() {
    const settings = await this.prisma.site_settings.findMany({
      where: { key: { in: ['telegram_bot_token', 'telegram_group_id', 'telegram_login_group_id'] } }
    });

    const config: Record<string, string> = {};
    for (const setting of settings) {
      if (setting.value) {
        config[setting.key] = setting.value;
      }
    }
    return config;
  }

  async sendMessage(chatId: string, text: string, token: string) {
    if (!chatId || !token) return false;

    try {
      const url = `https://api.telegram.org/bot${token}/sendMessage`;
      await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          chat_id: chatId,
          text,
          parse_mode: 'HTML'
        })
      });
      return true;
    } catch (e) {
      console.error('Telegram send error', e);
      return false;
    }
  }

  async sendLoginNotification(userEmail: string, ip: string, deviceName: string) {
    const config = await this.getTelegramConfig();
    const token = config['telegram_bot_token'];
    const chatId = config['telegram_login_group_id'] || config['telegram_group_id'];

    if (!token || !chatId) return false;

    const message = `🟢 <b>Desktop Login</b>\n\n👤 <b>User:</b> ${userEmail}\n💻 <b>Device:</b> ${deviceName}\n🌐 <b>IP:</b> ${ip}\n⏰ <b>Time:</b> ${new Date().toLocaleString()}`;
    return this.sendMessage(chatId, message, token);
  }

  async sendUsbNotification(userEmail: string, ip: string, deviceName: string, message: string) {
    const config = await this.getTelegramConfig();
    const token = config['telegram_bot_token'];
    const chatId = config['telegram_group_id'];

    if (!token || !chatId) return false;

    const text = `🚨 <b>USB Activity Detected</b>\n\n👤 <b>User:</b> ${userEmail}\n💻 <b>Device:</b> ${deviceName}\n🌐 <b>IP:</b> ${ip}\n⚠️ <b>Event:</b> ${message}\n⏰ <b>Time:</b> ${new Date().toLocaleString()}`;
    return this.sendMessage(chatId, text, token);
  }
}
