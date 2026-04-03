import { Controller, Post, Body, UnauthorizedException } from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';

@Controller('api/agent')
export class MonitoringController {
  constructor(private prisma: PrismaService) {}

  @Post('heartbeat')
  async heartbeat(@Body() body: any) {
    const { machine_id, current_version, user_id } = body;

    if (!machine_id) {
      throw new UnauthorizedException('Machine ID required');
    }

    const data: any = {
      last_seen: new Date(),
    };

    if (current_version) data.agent_version = current_version;
    if (user_id) data.last_logged_in_user_id = BigInt(user_id);

    try {
      await this.prisma.devices.update({
        where: { hardware_id: machine_id },
        data,
      });
    } catch (e) {
      // Device might not exist, silently ignore for heartbeat
    }

    return { success: true };
  }
}
