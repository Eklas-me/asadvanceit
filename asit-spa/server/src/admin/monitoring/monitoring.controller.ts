import { Controller, Get, UseGuards } from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';
import { JwtAuthGuard } from '../../auth/guards/jwt-auth.guard';
import { RolesGuard } from '../../auth/guards/roles.guard';
import { Roles } from '../../auth/decorators/roles.decorator';

@Controller('api/admin/monitoring')
@UseGuards(JwtAuthGuard, RolesGuard)
@Roles('admin')
export class MonitoringController {
  constructor(private prisma: PrismaService) {}

  @Get('devices')
  async getDevices() {
    const devices = await this.prisma.devices.findMany({
      include: {
        users_devices_user_idTousers: {
          select: { name: true, email: true }
        }
      },
      orderBy: { last_seen: 'desc' }
    });

    return {
      data: devices.map(d => ({
        ...d,
        id: d.id.toString(),
        user_id: d.user_id?.toString(),
        last_logged_in_user_id: d.last_logged_in_user_id?.toString(),
        worker: d.users_devices_user_idTousers ? {
          name: d.users_devices_user_idTousers.name,
          email: d.users_devices_user_idTousers.email
        } : null
      }))
    };
  }
}
