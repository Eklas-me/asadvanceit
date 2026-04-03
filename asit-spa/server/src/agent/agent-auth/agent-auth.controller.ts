import { Controller, Post, Body, UnauthorizedException, Req } from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';
import { AuthService } from '../../auth/auth.service';
import { TelegramService } from '../../services/telegram/telegram.service';

@Controller('api/agent')
export class AgentAuthController {
  constructor(
    private prisma: PrismaService,
    private authService: AuthService,
    private telegramService: TelegramService,
  ) {}

  @Post('login')
  async login(@Body() body: any, @Req() req: any) {
    const { email, password, machine_id, computer_name, agent_version } = body;

    const user = await this.authService.validateUser(email, password);
    if (!user) {
      throw new UnauthorizedException('Invalid credentials');
    }

    if (user.status !== 'active') {
      throw new UnauthorizedException('Account is not active');
    }

    // Register or update device
    let deviceValue: any = null;
    if (machine_id && computer_name) {
      deviceValue = await this.prisma.devices.upsert({
        where: { hardware_id: machine_id },
        update: {
          computer_name,
          agent_version: agent_version || 'unknown',
          last_seen: new Date(),
          last_logged_in_user_id: user.id,
          user_id: user.id
        },
        create: {
          hardware_id: machine_id,
          computer_name,
          agent_version: agent_version || 'unknown',
          last_seen: new Date(),
          last_logged_in_user_id: user.id,
          user_id: user.id
        }
      });
    }

    const loginData = await this.authService.login(user);

    // Send telegram notification
    const ip = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    this.telegramService.sendLoginNotification(email, ip, computer_name || 'Unknown Device');

    return {
      success: true,
      token: loginData.access_token,
      user: {
        id: loginData.user.id,
        name: loginData.user.name,
        email: loginData.user.email,
        shift: loginData.user.shift,
      },
      device: deviceValue ? {
        id: deviceValue.id.toString(),
        machine_id: deviceValue.hardware_id
      } : null
    };
  }
}
