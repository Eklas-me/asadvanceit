import { Controller, Post, Body, UseGuards } from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';
import { JwtAuthGuard } from '../../auth/guards/jwt-auth.guard';
import { RolesGuard } from '../../auth/guards/roles.guard';
import { Roles } from '../../auth/decorators/roles.decorator';

@Controller('api/admin/notifications')
@UseGuards(JwtAuthGuard, RolesGuard)
@Roles('admin')
export class NotificationsController {
  constructor(private prisma: PrismaService) {}

  @Post()
  async create(@Body() body: { title: string; message: string; user_id?: string }) {
    return this.prisma.notifications.create({
      data: {
        title: body.title,
        message: body.message,
        user_id: body.user_id ? BigInt(body.user_id) : null,
        status: 'unread'
      }
    });
  }
}
