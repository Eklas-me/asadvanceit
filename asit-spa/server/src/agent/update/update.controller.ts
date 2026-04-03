import { Controller, Get, Param, Res, HttpStatus } from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';
import type { Response } from 'express';

@Controller('api/updates')
export class UpdateController {
  constructor(private prisma: PrismaService) {}

  @Get(':target/:v')
  async checkUpdate(@Param('target') target: string, @Param('v') version: string, @Res() res: Response) {
    const activeVersionSetting = await this.prisma.site_settings.findUnique({
      where: { key: 'desktop_agent_version' }
    });

    const activeVersion = activeVersionSetting?.value || '1.0.0';

    if (version !== activeVersion) {
      const downloadUrlSetting = await this.prisma.site_settings.findUnique({
        where: { key: 'desktop_agent_download_url' }
      });
      const downloadUrl = downloadUrlSetting?.value || '';

      const signatureSetting = await this.prisma.site_settings.findUnique({
        where: { key: 'desktop_agent_signature' }
      });
      const signature = signatureSetting?.value || '';

      const notesSetting = await this.prisma.site_settings.findUnique({
        where: { key: 'desktop_agent_release_notes' }
      });
      const notes = notesSetting?.value || 'New version available.';

      return res.status(HttpStatus.OK).json({
        version: activeVersion,
        notes,
        pub_date: new Date().toISOString(),
        platforms: {
          'windows-x86_64': {
            signature,
            url: downloadUrl
          }
        }
      });
    }

    return res.status(HttpStatus.NO_CONTENT).send();
  }
}
